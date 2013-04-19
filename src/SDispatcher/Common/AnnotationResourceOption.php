<?php
namespace SDispatcher\Common;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use SDispatcher\Common\Annotation\AbstractAnnotation;

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

    public function __construct()
    {
        AnnotationRegistry::registerAutoloadNamespace(
            static::ANNOTATION_NAMESPACE, realpath(__DIR__ . '/../../'));
        $this->annotationReader = new AnnotationReader();
    }

    public function setTarget($classOrObj, $method)
    {
        $this->reflectionClass = new \ReflectionClass($classOrObj);
        $this->reflectionMethod = new \ReflectionMethod($classOrObj, $method);
    }

    public function willPaginate()
    {
        $this->tryReadOption(
            'willPaginate',
            $out,
            false);
        return $out;
    }

    public function getPaginatorClass()
    {
        $this->tryReadOption(
            'paginatorClass',
            $out,
            'SDispatcher\\Common\\InMemoryPaginator');
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    protected function tryReadOption($name, &$out, $default = null)
    {
        $out = $default;

        if (!$this->reflectionClass || !$this->reflectionMethod) {
            return false;
        }

        try {
            $annotationName = static::ANNOTATION_NAMESPACE . ucfirst($name);

            try {
                // Inspects method first, since method has priority over class
                $methodAnnotation = $this
                    ->annotationReader
                    ->getMethodAnnotation(
                        $this->reflectionMethod,
                        $annotationName);

                if ($methodAnnotation
                    && $methodAnnotation instanceof AbstractAnnotation
                ) {
                    $out = $methodAnnotation->values();
                    return true;
                }
            } catch (\Exception $ex) {
            }

            // If we do not have method annotation,
            // then look at the class annotation
            $class = $this->reflectionClass;
            while ($class) {
                try {
                    $classAnnotation = $this
                        ->annotationReader
                        ->getClassAnnotation(
                            $class,
                            $annotationName);
                    if ($classAnnotation
                        && $classAnnotation instanceof AbstractAnnotation
                    ) {
                        $out = $classAnnotation->values();
                        return true;
                    }
                } catch (\Exception $ex) {
                }
                $class = $class->getParentClass();
            }
        } catch (\Exception $ex) {
            // Lazily catch any exception and return false, and set
            // $out to default value
            $out = $default;
            return false;
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
