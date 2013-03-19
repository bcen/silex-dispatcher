<?php
namespace SDispatcher\Common\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class WillPaginate extends AbstractAnnotation
{
    public $flag = true;

    public function values()
    {
        return $this->flag;
    }
}
