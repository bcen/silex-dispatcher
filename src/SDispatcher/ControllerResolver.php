<?php
namespace SDispatcher;

use Symfony\Component\HttpFoundation\Request;
use Silex\ControllerResolver as SilexControllerResolver;

/**
 * Extends the base resolver to include resovling dependency from container by
 * class name or argument name.
 */
class ControllerResolver extends SilexControllerResolver
{
    protected function doGetArguments(Request $request, $controller, array $parameters)
    {
        foreach ($parameters as $p) {
            $name = $p->getName();
            if (isset($this->app[$name])) {
                $request->attributes->set($name, $this->app[$name]);
            } elseif ($p->getClass()) {
                $className = $p->getClass()->getName();
                if (isset($this->app[$className])) {
                    $request->attributes->set($name, $this->app[$className]);
                }
            }
        }

        return parent::doGetArguments($request, $controller, $parameters);
    }
}
