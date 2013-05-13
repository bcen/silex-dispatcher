<?php
namespace SDispatcher;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCbvController
{
    /**
     * The container
     * @var mixed
     */
    private $container;

    public function dispatch(Request $request, $container)
    {
        $this->container = $container;
        return static::doDispatch($this, $request, $container);
    }

    public static function doDispatch($controller, Request $request, $container)
    {
        $method = strtolower($request->getMethod());

        if (method_exists($controller, $method)) {
            return $controller->$method($request, $container);
        } elseif (method_exists($controller, 'handleRequest')) {
            return $controller->{'handleRequest'}($request, $container);
        }

        return new Response('', 405);
    }

    public function make($id)
    {
        return $this->container[$id];
    }
}
