<?php
namespace SDispatcher\Common;

use ReflectionClass;
use ArrayAccess;
use LogicException;
use SDispatcher\Common\Exception\NoDependencyFoundException;

/**
 * Used to create class and resolves its dependencies.
 */
class ClassResolver
{
    /**
     * Array of container for dependency lookup.
     * Note: Container must be an array or \ArrayAccess.
     * @var array
     */
    protected $containers = array();

    /**
     * @var array
     */
    protected $callbacks = array();

    /**
     * Creates an instance with the specified dependencies.
     * @throws \LogicException
     */
    public function __construct(/* $arg0[, $...] */)
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (is_array($arg) || $arg instanceof ArrayAccess) {
                $this->containers[] = $arg;
            }
        }
    }

    /**
     * Creates an instance of the specified $class and injects its
     * dependencie(s) into the constructor.
     * @param string $class
     * @return mixed
     * @throws \SDispatcher\Common\Exception\NoDependencyFoundException
     */
    public function create($class)
    {
        $classReflector = new ReflectionClass($class);
        $ctor = $classReflector->getConstructor();

        // if constructor found, get all the parameters
        // for dependency injection
        $expectedParams = array();
        if ($ctor) {
            $expectedParams = $ctor->getParameters();
        }

        $deps = array();
        foreach ($expectedParams as $param) {
            $notFound = true;
            $depName = $param->getName();

            foreach ($this->containers as $container) {
                if (isset($container[$depName])) {
                    $deps[] = $container[$depName];
                    $notFound = false;
                    break;
                }
            }

            if ($notFound) {
                throw new NoDependencyFoundException(
                    "No \"$depName\" found in Pimple container."
                );
            }
        }

        $class = $classReflector->newInstanceArgs($deps);

        foreach ($this->callbacks as $cb) {
            $cb($this->containers, $class);
        }

        return $class;
    }

    /**
     * Adds a onFinish event callback.
     * @param callable $callback
     */
    public function onFinish(\Closure $callback)
    {
        $this->callbacks[] = $callback;
    }
}
