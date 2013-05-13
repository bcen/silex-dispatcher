<?php
namespace SDispatcher;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for django's CBV-like controller.
 */
abstract class AbstractCbvController
{
    /**
     * The container
     * @var mixed
     */
    private $container;

    /**
     * @see doDispatch()
     */
    public function dispatch(Request $request, $container)
    {
        $this->container = $container;
        return static::doDispatch($this, $request, $container);
    }

    /**
     * Dispatches the `$request` to the appropriate `$controller` method handler.
     * If no method handler found, it will dispatch the `$request` to
     * `handleRequest` method. If `handleRequest` is not implemented either, then
     * an empty 405 response will be returned.
     *
     * @param mixed $controller
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $container
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * Retrieves services/parameters from the container
     * @param string $id
     * @return mixed
     */
    public function make($id)
    {
        return $this->container[$id];
    }
}
