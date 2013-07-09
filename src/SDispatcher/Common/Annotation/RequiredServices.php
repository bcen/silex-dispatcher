<?php
namespace SDispatcher\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class RequiredServices extends AbstractAnnotation
{
    public $services = array();

    public function values()
    {
        return (array)$this->services;
    }
}
