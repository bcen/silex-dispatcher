<?php
namespace SDispatcher\Tests;

use SDispatcher\ControllerResolver;
use SDispatcher\Tests\Fixture\ResolveMePlease;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\ServiceControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControllerResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_resolve_by_class_name_from_container()
    {
        $app = new Application();
        $app['SDispatcher\\Tests\\Fixture\\ResolveMePlease'] = $app->share(function () {
            return new ResolveMePlease();
        });
        $controller = function (ResolveMePlease $obj) { };

        $controllerResolver = new ControllerResolver($app['resolver'], $app);
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
        $app = new Application();
        $app['arcphssFlag'] = true;

        $controller = function ($arcphssFlag) { };

        $controllerResolver = new ControllerResolver($app['resolver'], $app);
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
        $app = new Application();

        $controller = function ($arcphssFlag = 'flag') { };

        $controllerResolver = new ControllerResolver($app['resolver'], $app);
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
        $app = new Application();

        $app['SDispatcher\\Tests\\Fixture\\ResolveMePlease'] = $app->share(function () {
            return new ResolveMePlease();
        });
        $app['obj'] = new \stdClass();

        $controller = function (ResolveMePlease $obj) { };

        $controllerResolver = new ControllerResolver($app['resolver'], $app);
        $args = $controllerResolver->getArguments(
            Request::create('/'),
            $controller);

        $this->assertTrue(is_array($args) && !empty($args));
        $this->assertInstanceOf('stdClass', $args[0]);
    }

    /**
     * @test
     */
    public function it_should_resolve_even_arguments_have_default_value_but_exists_in_container()
    {
        $app = new Application();
        $app['SDispatcher\\Tests\\Fixture\\ResolveMePlease'] = $app->share(function () {
            return new ResolveMePlease();
        });
        $controller = function (ResolveMePlease $obj = null) { };

        $controllerResolver = new ControllerResolver($app['resolver'], $app);
        $args = $controllerResolver->getArguments(
            Request::create('/'),
            $controller);

        $this->assertNotEmpty($args);
        $this->assertNotNull($args[0]);
    }

    /**
     * @test
     */
    public function it_should_be_compatible_with_ServiceControllerResolver()
    {
        $app = new Application();
        $app['SDispatcher\\Tests\\Fixture\\ResolveMePlease'] = $app->share(function () {
            return new ResolveMePlease();
        });
        $app->register(new ServiceControllerServiceProvider());
        $app['resolver'] = $app->share($app->extend(
            'resolver',
            function ($resolver, $app) {
                return new ControllerResolver($resolver, $app);
            }));
        $app->get('/', function (ResolveMePlease $obj = null) {
            if (!$obj) {
                return new Response('', 404);
            }
            return 'whatever';
        });

        $response = $app->handle(Request::create('/'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_should_be_compatible_with_ServiceControllerServiceProvider_and_order_does_not_matter()
    {
        $app = new Application();
        $app['SDispatcher\\Tests\\Fixture\\ResolveMePlease'] = $app->share(function () {
            return new ResolveMePlease();
        });
        $app['resolver'] = $app->share($app->extend(
            'resolver',
            function ($resolver, $app) {
                return new ControllerResolver($resolver, $app);
            }));
        $app->register(new ServiceControllerServiceProvider());
        $app['dummy_controller'] = $app->share(function ($c) {
            return new DummyControllerAgain();
        });
        $app->get('/', 'dummy_controller:method1');

        $response = $app->handle(Request::create('/'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function functional_test()
    {
        $app = new Application();
        $app['resolver'] = $app->share($app->extend('resolver', function ($resolver, $app) {
            return new ControllerResolver($resolver, $app);
        }));

        $app['SDispatcher\\Tests\\Fixture\\ResolveMePlease'] = function () {
            return new ResolveMePlease();
        };
        $app->get('/', function (ResolveMePlease $obj) {
            return $obj->method1();
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals(200, $response->getStatusCode());
    }
}

class DummyControllerAgain
{
    public function method1(ResolveMePlease $obj = null)
    {
        if (!$obj) {
            return new Response('', 404);
        }
        return 'whatever';
    }
}
