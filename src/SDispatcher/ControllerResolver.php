<?php
namespace SDispatcher;

use Symfony\Component\HttpFoundation\Request;
use Silex\ControllerResolver as SilexControllerResolver;
use Silex\Application;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use SDispatcher\Common\Annotation\SupportedFormats;

/**
 * Extends the base resolver to include resovling dependency from container by
 * class name or argument name.
 */
class ControllerResolver extends SilexControllerResolver
{
    protected function doGetArguments(Request $request, $controller, array $parameters)
    {
        AnnotationRegistry::registerAutoloadNamespace(
            'SDispatcher\\Common\\Annotation', realpath(__DIR__ . '/../'));
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
        $options = array();
        if (is_object($controller)) {
            $annotationReader = new AnnotationReader();
            $anno = $annotationReader->getClassAnnotations(
                new \ReflectionClass($controller));
            foreach ($anno as $a) {
                if ($a instanceof SupportedFormats) {
                    $options['sdispatcher.controller.supportedFormats'] =
                        (array)$a->formats;
                }
            }
        }
        return $options;
    }
}
