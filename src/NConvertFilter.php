<?php
namespace RDM\Assetic\Filter;

use Assetic\Filter\BaseProcessFilter;
use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;

/**
 * Filter for NConvert
 */
class NConvertFilter extends BaseProcessFilter
{
    const FORMAT_GIF = 'gif';
    const FORMAT_JPEG = 'jpeg';
    const FORMAT_PNG = 'png';

    private $nconvertBin;

    private $format;

    /**
     * Constructs the filter
     * 
     * @param string $nconvertBin The path to the NConvert binary
     */
    public function __construct($nconvertBin = '/usr/bin/nconvert')
    {
        $this->nconvertBin = $nconvertBin;
    }

    /**
     * Sets the output format
     * 
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function filterLoad(AssetInterface $asset)
    {}

    public function filterDump(AssetInterface $asset)
    {
        $pb = $this->createProcessBuilder(array(
            $this->nconvertBin
        ));
        
        $input = tempnam(sys_get_temp_dir(), 'assetic_nconvert');
        $output = sys_get_temp_dir() . '/' . basename($input, '.tmp') . '.' . $this->format;
        
        file_put_contents($input, $asset->getContent());
        
        $pb->add('-out');
        $pb->add($this->format);
        $pb->add('-o');
        $pb->add($output);
        $pb->add($input);
        
        $proc = $pb->getProcess();
        $code = $proc->run();
        
        unlink($input);
        
        if ($code !== 0) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }
        
        $asset->setContent(file_get_contents($output));
        
        unlink($output);
    }
}
