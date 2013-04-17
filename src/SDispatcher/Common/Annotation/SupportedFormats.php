<?php
namespace SDispatcher\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class SupportedFormats extends AbstractAnnotation
{
    public $formats = array('json');

    public function values()
    {
        return (array)$this->formats;
    }
}
