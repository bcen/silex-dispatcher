<?php
namespace SDispatcher\Common\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class ResourceIdentifier extends AbstractAnnotation
{
    public $identifier = 'id';

    public function values()
    {
        return $this->identifier;
    }
}
