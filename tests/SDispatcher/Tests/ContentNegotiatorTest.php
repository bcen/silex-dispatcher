<?php
namespace SDispatcher\Tests;

use SDispatcher\ContentNegotiator;
use SDispatcher\RouteOptionInspector;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ContentNegotiatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_return_406_response_if_no_accept_header_available()
    {
        $request = Request::create('/r');
        $request->headers->set('Accept', 'application/whatever');
        $app = new Application();
        $app->before(new RouteOptionInspector($app));
        $app->before(new ContentNegotiator($app));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method4');
        $response = $app->handle($request);
        $this->assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function route_should_have_accepted_format_from_accept_header()
    {
        $request = Request::create('/r');
        $request->headers->set('Accept', 'application/json');
        $app = new Application();
        $app->before(new RouteOptionInspector($app));
        $app->before(new ContentNegotiator($app));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method4');
        $response = $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals('application/json', $route->getOption('sdispatcher.route.accepted_format'));
    }

    /**
     * @test
     */
    public function route_should_have_accepted_format_from_query_string()
    {
        $request = Request::create('/r?format=application/json');
        $app = new Application();
        $app->before(new RouteOptionInspector($app));
        $app->before(new ContentNegotiator($app));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method4');
        $response = $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals('application/json', $route->getOption('sdispatcher.route.accepted_format'));
    }

    /**
     * @test
     */
    public function route_should_have_accepted_format_from_shorthand_query_string()
    {
        $request = Request::create('/r?format=json');
        $app = new Application();
        $app->before(new RouteOptionInspector($app));
        $app->before(new ContentNegotiator($app));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method4');
        $response = $app->handle($request);
        $routeName = $request->attributes->get('_route');
        $route = $app['routes']->get($routeName);
        $this->assertEquals('application/json', $route->getOption('sdispatcher.route.accepted_format'));
    }
}
