<?php
namespace SDispatcher\Tests;

use SDispatcher\ControllerResolver;
use SDispatcher\Tests\Fixture\ResolveMePlease;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

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
        $app = new Application();
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
        $app = new Application();

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
        $app = new Application();

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
    public function functional_test()
    {
        $app = new Application();
        $app['resolver'] = new ControllerResolver($app);
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
