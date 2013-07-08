<?php
namespace SDispatcher\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class After extends AbstractAnnotation
{
    public $middlewares = array();

    public function values()
    {
        return (array)$this->middlewares;
    }
}
