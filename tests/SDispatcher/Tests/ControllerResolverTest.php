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
