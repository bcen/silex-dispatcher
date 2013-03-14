<?php
namespace SDispatcher\Tests\Middleware;

use SDispatcher\Common\RouteOptions;
use SDispatcher\Middleware\RouteOptionInspector;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class RouteOptionInspectorTest extends AbstractMiddlewareTestCaseHelper
{
    /**
     * @test
     */
    public function it_should_resolve_option_on_class_annotation()
    {
        $request = Request::create('/r');
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method1');
        $app->handle($request);
        $route = $this->getCurrentRoute($app, $request);
        $this->assertEquals(
            100,
            $route->getOption(RouteOptions::PAGE_LIMIT));
    }

    /**
     * @test
     */
    public function it_should_resolve_option_on_method_over_class_annotation()
    {
        $request = Request::create('/r');
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method3');
        $app->handle($request);
        $route = $this->getCurrentRoute($app, $request);
        $this->assertEquals(
            10,
            $route->getOption(RouteOptions::PAGE_LIMIT));
    }

    /**
     * @test
     */
    public function it_should_resolve_to_default_values_if_no_annotation_at_all()
    {
        $request = Request::create('/a/wow');
        $app = new Application();
        $app['debug'] = true;
        $app->before(new RouteOptionInspector($app['routes']));
        $app->get('/a/wow', 'SDispatcher\\Tests\\Fixture\\ResolveMePlease::method1');
        $app->handle($request);
        $route = $this->getCurrentRoute($app, $request);

        $this->assertEquals(
            array(
                array('application/json'),
                'id',
                20,
                false
            ),
            array(
                $route->getOption(RouteOptions::SUPPORTED_FORMATS),
                $route->getOption(RouteOptions::RESOURCE_ID),
                $route->getOption(RouteOptions::PAGE_LIMIT),
                $route->getOption(RouteOptions::WILL_PAGINGATE)
            ));
    }
}
