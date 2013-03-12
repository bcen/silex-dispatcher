<?php
namespace SDispatcher;

use SDispatcher\Common\AnnotationResourceOption;
use Symfony\Component\HttpFoundation\Request;
use Silex\ControllerResolver as SilexControllerResolver;
use Silex\Application;

/**
 * Extends the base resolver to include resovling dependency from container by
 * class name or argument name.
 */
class ControllerResolver extends SilexControllerResolver
{
    protected function doGetArguments(Request $request, $controller, array $parameters)
    {
        if (is_array($controller)
            && count($controller) >= 2
            && $routeName = $request->attributes->get('_route')
        ) {
            $options = $this->resolveControllerOptions(
                $controller[0],
                $controller[1]);
            $route = $this->app['routes']->get($routeName);
            foreach ((array)$options as $key => $value) {
                $route->setOption($key, $value);
            }
        }

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

    protected function resolveControllerOptions($controller, $method)
    {
        $resourceOption = new AnnotationResourceOption($controller, $method);
        $options = array(
            'sdispatcher.route.supportedFormats' => $resourceOption->getSupportedFormats()
        );
        return $options;
    }
}
