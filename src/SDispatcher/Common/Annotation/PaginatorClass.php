<?php
namespace SDispatcher\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class PaginatorClass extends AbstractAnnotation
{
    public $class = 'SDispatcher\\Common\\InMemoryPaginator';

    public function values()
    {
        return (string)$this->class;
    }
}
