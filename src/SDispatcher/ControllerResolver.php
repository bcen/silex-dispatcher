<?php
namespace SDispatcher;

use Silex\Application;
use Silex\ControllerResolver as SilexControllerResolver;
use Symfony\Component\HttpFoundation\Request;

/**
 * Extends the base resolver to include resovling dependency from container by
 * class name or argument name.
 */
class ControllerResolver extends SilexControllerResolver
{
    /**
     * {@inheritdoc}
     */
    protected function doGetArguments(Request $request, $controller, array $parameters)
    {
        /* @var \ReflectionClass|\ReflectionParameter $p */
        foreach ($parameters as $p) {
            $name = $p->getName();
            if (isset($this->app[$name])) {
                $request->attributes->set($name, $this->app[$name]);
            } elseif ($p->getClass()) {
                /* @var \ReflectionClass $reflectedClass */
                $reflectedClass = $p->getClass();
                $className = $reflectedClass->getName();
                if (isset($this->app[$className])) {
                    $request->attributes->set($name, $this->app[$className]);
                }
            }
        }

        return parent::doGetArguments($request, $controller, $parameters);
    }
}
