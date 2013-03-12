<?php
namespace SDispatcher\Tests;

use SDispatcher\Tests\Fixture\ResolveMePlease;
use SDispatcher\ControllerResolver;
use Symfony\Component\HttpFoundation\Request;

class ControllerResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_resolve_by_class_name_from_container()
    {
        $app = new \Silex\Application();
        $app['SDispatcher\\Tests\\Fixture\\ResolveMePlease'] = $app->share(function () {
            return new ResolveMePlease();
        });
        $controller = function (ResolveMePlease $obj) { };

        $controllerResolver = new ControllerResolver($app);
        $args = $controllerResolver->getArguments(
            Request::create('/'),
            $controller);

        $this->assertTrue(is_array($args) && !empty($args));
        $this->assertInstanceOf('SDispatcher\\Tests\\Fixture\\ResolveMePlease', $args[0]);
    }

    /**
     * @test
     */
    public function it_should_resolve_by_name_from_container()
    {
        $app = new \Silex\Application();
        $app['arcphssFlag'] = true;

        $controller = function ($arcphssFlag) { };

        $controllerResolver = new ControllerResolver($app);
        $args = $controllerResolver->getArguments(
            Request::create('/'),
            $controller);

        $this->assertTrue(is_array($args) && !empty($args));
        $this->assertTrue($args[0]);
    }

    /**
     * @test
     */
    public function it_should_resolve_default_value()
    {
        $app = new \Silex\Application();

        $controller = function ($arcphssFlag = 'flag') { };

        $controllerResolver = new ControllerResolver($app);
        $args = $controllerResolver->getArguments(
            Request::create('/'),
            $controller);

        $this->assertTrue(is_array($args) && !empty($args));
        $this->assertEquals('flag', $args[0]);
    }

    /**
     * @test
     */
    public function it_should_resolve_by_name_over_by_class_name()
    {
        $app = new \Silex\Application();

        $app['SDispatcher\\Tests\\Fixture\\ResolveMePlease'] = $app->share(function () {
            return new ResolveMePlease();
        });
        $app['obj'] = new \stdClass();

        $controller = function (ResolveMePlease $obj) { };

        $controllerResolver = new ControllerResolver($app);
        $args = $controllerResolver->getArguments(
            Request::create('/'),
            $controller);

        $this->assertTrue(is_array($args) && !empty($args));
        $this->assertInstanceOf('stdClass', $args[0]);
    }

    /**
     * @test
     */
    public function it_should_resolve_class_annotation_to_route_options()
    {
        $request = Request::create('/a/wow');
        $app = new \Silex\Application();
        $app['resolver'] = new ControllerResolver($app);
        $app->get('/a/wow', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method1');
        $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals(
            array('application/json', 'application/xml'),
            $route->getOption('sdispatcher.route.supported_formats'));
    }

    /**
     * @test
     */
    public function it_should_resolve_method_annotation_over_class_annotation_to_route_options()
    {
        $request = Request::create('/a/wow');
        $app = new \Silex\Application();
        $app['resolver'] = new ControllerResolver($app);
        $app->get('/a/wow', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method2');
        $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals(
            array('application/xml'),
            $route->getOption('sdispatcher.route.supported_formats'));
    }

    /**
     * @test
     */
    public function it_should_resolve_resource_identifier_route_option()
    {
        $request = Request::create('/a/wow');
        $app = new \Silex\Application();
        $app['resolver'] = new ControllerResolver($app);
        $app->get('/a/wow', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method3');
        $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals(
            'my_resource_identifier',
            $route->getOption('sdispatcher.route.resource_identifier'));
    }

    /**
     * @test
     */
    public function it_should_resolve_page_limit_route_option()
    {
        $request = Request::create('/a/wow');
        $app = new \Silex\Application();
        $app['resolver'] = new ControllerResolver($app);
        $app->get('/a/wow', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method2');
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
    public function it_should_resolve_will_paginate_as_true_if_method_annotate_it()
    {
        $request = Request::create('/a/wow');
        $app = new \Silex\Application();
        $app['resolver'] = new ControllerResolver($app);
        $app->get('/a/wow', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method4');
        $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals(
            true,
            $route->getOption('sdispatcher.route.will_paginate'));
    }

    /**
     * @test
     */
    public function it_should_resolve_will_paginate_as_false_if_no_method_annotate_it()
    {
        $request = Request::create('/a/wow');
        $app = new \Silex\Application();
        $app['resolver'] = new ControllerResolver($app);
        $app->get('/a/wow', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method1');
        $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals(
            false,
            $route->getOption('sdispatcher.route.will_paginate'));
    }

    /**
     * @test
     */
    public function functional_test()
    {
        $app = new \Silex\Application();
        $app['resolver'] = new ControllerResolver($app);
        $app['SDispatcher\\Tests\\Fixture\\ResolveMePlease'] = function () {
            return new ResolveMePlease();
        };
        $app->get('/', function (ResolveMePlease $obj) {
            return '';
        });
        $response = $app->handle(Request::create('/'));
        $this->assertEquals(200, $response->getStatusCode());
    }
}
