<?php
namespace SDispatcher\Tests\Middleware;

use SDispatcher\Middleware\ArrayToDataResponseListener;
use SDispatcher\Middleware\ContentNegotiator;
use SDispatcher\Middleware\RouteOptionInspector;
use SDispatcher\Middleware\SerializationInspector;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ArrayToDataResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_convert_array_to_data_response_for_serialization()
    {
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->before(new ContentNegotiator($app['routes']));
        $app->after(new SerializationInspector($app['routes']));
        $request = Request::create('/r.json');

        $app['dispatcher']->addSubscriber(new ArrayToDataResponseListener());
        $app->get('/r.{_format}', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method7');
        $response = $app->handle($request);

        $this->assertEquals('{"name":"method7"}', $response->getContent());
    }
}
