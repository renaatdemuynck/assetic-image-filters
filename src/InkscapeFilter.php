<?php
namespace RDM\Assetic\Filter;

use Assetic\Filter\BaseProcessFilter;
use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;

/**
 * Filter for NConvert
 */
class InkscapeFilter extends BaseProcessFilter
{
    const FORMAT_PNG = 'png';
    const FORMAT_PS = 'ps';
    const FORMAT_EPS = 'eps';
    const FORMAT_PDF = 'pdf';
    const FORMAT_EMF = 'emf';

    private $inkscapeBin;

    private $format;

    /**
     * Constructs the filter
     * 
     * @param string $inkscapeBin The path to the NConvert binary
     */
    public function __construct($inkscapeBin = '/usr/bin/nkscape')
    {
        $this->inkscapeBin = $inkscapeBin;
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
            $this->inkscapeBin
        ));
        
        $input = tempnam(sys_get_temp_dir(), 'assetic_nconvert');
        $output = sys_get_temp_dir() . '/' . basename($input, '.tmp') . '.' . $this->format;
        
        file_put_contents($input, $asset->getContent());
        
        $pb->add('-z');
        $pb->add('--export-' . $this->format);
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
