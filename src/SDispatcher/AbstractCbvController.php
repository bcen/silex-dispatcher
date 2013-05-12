<?php
namespace SDispatcher;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCbvController
{
    private $container;

    public function dispatch(Request $request, Application $app)
    {
        $this->container = $app;
        return static::doDispatch($this, $request, $app);
    }

    public static function doDispatch($controller, Request $request, Application $app)
    {
        $method = strtolower($request->getMethod());

        if (method_exists($controller, $method)) {
            return $controller->$method($request, $app);
        } elseif (method_exists($controller, 'handleRequest')) {
            return $controller->{'handleRequest'}($request, $app);
        }

        return new Response('', 405);
    }

    public function make($id)
    {
        return $this->container[$id];
    }
}
