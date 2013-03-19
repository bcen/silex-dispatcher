<?php
namespace SDispatcher\Tests\Middleware;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractMiddlewareTestCaseHelper extends \PHPUnit_Framework_TestCase
{
    /**
     * Util method for getting current `Route` object.
     * @param \Silex\Application $app
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\Routing\Route
     */
    protected function getCurrentRoute(Application $app, Request $request)
    {
        /* @var \Symfony\Component\Routing\RouteCollection $routes */
        $routes = $app['routes'];
        return $routes->get($request->attributes->get('_route'));
    }
}
