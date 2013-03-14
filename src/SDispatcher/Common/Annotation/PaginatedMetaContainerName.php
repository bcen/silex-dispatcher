<?php
namespace SDispatcher\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class PaginatedMetaContainerName extends AbstractAnnotation
{
    public $name = 'meta';

    public function values()
    {
        return (string)$this->name;
    }
}
