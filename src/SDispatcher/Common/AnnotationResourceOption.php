<?php
namespace SDispatcher\Common;

use SDispatcher\Common\Annotation\AbstractAnnotation;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationResourceOption extends AbstractResourceOption
{
    /**
     * @var string
     */
    const ANNOTATION_NAMESPACE = 'SDispatcher\\Common\\Annotation\\';

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;

    /**
     * @var \ReflectionMethod
     */
    protected $reflectionMethod;

    public function __construct($classOrObj, $method)
    {
        AnnotationRegistry::registerAutoloadNamespace(
            static::ANNOTATION_NAMESPACE, realpath(__DIR__ . '/../../'));
        $this->reflectionClass = new \ReflectionClass($classOrObj);
        $this->reflectionMethod = new \ReflectionMethod($classOrObj, $method);
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * {@inheritdoc}
     */
    protected function tryReadOption($name, &$out, $default = null)
    {
        $out = $default;
        $annotationName = static::ANNOTATION_NAMESPACE . ucfirst($name);

        // Inspects method first, since method has priority over class
        $methodAnnotation = $this->annotationReader->getMethodAnnotation(
            $this->reflectionMethod,
            $annotationName);

        if ($methodAnnotation
            && $methodAnnotation instanceof AbstractAnnotation
        ) {
            $out = $methodAnnotation->values();
            return true;
        }

        // If we do not have method annotation,
        // then look at the class annotation
        $classAnnotation = $this->annotationReader->getClassAnnotation(
            $this->reflectionClass,
            $annotationName);

        if ($classAnnotation
            && $classAnnotation instanceof AbstractAnnotation
        ) {
            $out = $classAnnotation->values();
            return true;
        }

        // sad, no method nor class annotation?
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function tryWriteOption($name, $value)
    {
        // we don't write
    }
}
