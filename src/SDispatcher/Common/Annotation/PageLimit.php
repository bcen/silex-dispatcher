<?php
namespace SDispatcher\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class PageLimit extends AbstractAnnotation
{
    public $limit = 20;

    public function values()
    {
        return (int)$this->limit;
    }
}
