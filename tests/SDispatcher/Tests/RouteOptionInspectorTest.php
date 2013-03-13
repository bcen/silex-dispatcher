<?php
namespace SDispatcher\Tests;

use SDispatcher\RouteOptionInspector;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

class RouteOptionInspectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_resolve_page_limit_on_class_annotation()
    {
        $request = Request::create('/r');
        $app = new Application();
        $app->before(new RouteOptionInspector($app));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method1');
        $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals(
            100,
            $route->getOption('sdispatcher.route.page_limit'));
    }

    /**
     * @test
     */
    public function it_should_resolve_page_limit_on_method_over_class_annotation()
    {
        $request = Request::create('/r');
        $app = new Application();
        $app->before(new RouteOptionInspector($app));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method3');
        $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals(
            10,
            $route->getOption('sdispatcher.route.page_limit'));
    }

    /**
     * @test
     */
    public function it_should_resolve_to_default_values_if_no_annotation_at_all()
    {
        $request = Request::create('/a/wow');
        $app = new \Silex\Application();
        $app->before(new RouteOptionInspector($app));
        $app->get('/a/wow', 'SDispatcher\\Tests\\Fixture\\ResolveMePlease::method1');
        $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals(
            array(
                array('application/json'),
                'id',
                20,
                false
            ),
            array(
                $route->getOption('sdispatcher.route.supported_formats'),
                $route->getOption('sdispatcher.route.resource_identifier'),
                $route->getOption('sdispatcher.route.page_limit'),
                $route->getOption('sdispatcher.route.will_paginate')
            ));
    }
}
