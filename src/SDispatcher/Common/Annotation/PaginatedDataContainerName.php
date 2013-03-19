<?php
namespace SDispatcher\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class PaginatedDataContainerName extends AbstractAnnotation
{
    public $name = 'objects';

    public function values()
    {
        return (string)$this->name;
    }
}
