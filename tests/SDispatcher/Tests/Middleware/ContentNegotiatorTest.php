<?php
namespace SDispatcher\Tests\Middleware;

use SDispatcher\Common\RouteOptions;
use SDispatcher\Middleware\ContentNegotiator;
use SDispatcher\Middleware\RouteOptionInspector;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ContentNegotiatorTest extends AbstractMiddlewareTestCaseHelper
{
    /**
     * @test
     */
    public function it_should_return_406_response_if_no_accepted_format_available()
    {
        $request = Request::create('/r');
        $request->headers->set('Accept', 'application/whatever');
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->before(new ContentNegotiator($app['routes']));
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
        $app->before(new RouteOptionInspector($app['routes']));
        $app->before(new ContentNegotiator($app['routes']));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method4');
        $app->handle($request);
        $route = $this->getCurrentRoute($app, $request);
        $this->assertEquals('application/json', $route->getOption(RouteOptions::ACCEPTED_FORMAT));
    }

    /**
     * @test
     */
    public function route_should_have_accepted_format_from_query_string()
    {
        $request = Request::create('/r?format=application/json');
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->before(new ContentNegotiator($app['routes']));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method4');
        $app->handle($request);
        $route = $this->getCurrentRoute($app, $request);
        $this->assertEquals('application/json', $route->getOption(RouteOptions::ACCEPTED_FORMAT));
    }

    /**
     * @test
     */
    public function route_should_have_accepted_format_from_shorthand_query_string()
    {
        $request = Request::create('/r?format=json');
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->before(new ContentNegotiator($app['routes']));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method4');
        $app->handle($request);
        $route = $this->getCurrentRoute($app, $request);
        $this->assertEquals('application/json', $route->getOption(RouteOptions::ACCEPTED_FORMAT));
    }
}
