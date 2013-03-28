<?php
namespace SDispatcher\Tests\Middleware;

use SDispatcher\Middleware\ArrayToDataResponseListener;
use SDispatcher\Middleware\ContentNegotiator;
use SDispatcher\Middleware\PaginationListener;
use SDispatcher\Middleware\RouteOptionInspector;
use SDispatcher\Middleware\SerializationInspector;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaginationListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_paginate_the_data()
    {
        $request = Request::create('/r');
        $request->headers->set('Accept', 'application/json');
        $app = new Application();
        $app['debug'] = true;
        $app['dispatcher']->addSubscriber(new PaginationListener($app['routes']));
        $app['dispatcher']->addSubscriber(new ArrayToDataResponseListener());
        $app->before(new RouteOptionInspector($app['routes'], $app['resolver']));
        $app->before(new ContentNegotiator($app['routes']));
        $app->after(new SerializationInspector($app['routes']));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method8');

        $response = $app->handle($request);
        $actual = json_decode($response->getContent(), true);

        $this->assertEquals(
            array(
                0,
                5,
                11,
                array(1, 2, 3, 4, 5)
            ),
            array(
                $actual['meta']['offset'],
                $actual['meta']['limit'],
                $actual['meta']['total'],
                $actual['objects']
            ));
    }
}
