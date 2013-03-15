<?php
namespace SDispatcher\Tests\Middleware;

use SDispatcher\Middleware\ContentNegotiator;
use SDispatcher\Middleware\RouteOptionInspector;
use SDispatcher\Middleware\SerializationInspector;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class SerializationInspectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_serialize_to_json_response()
    {
        $request = Request::create('/r');
        $request->headers->set('Accept', 'application/json');
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->before(new ContentNegotiator($app['routes']));
        $app->after(new SerializationInspector($app['routes']));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method5');
        $response = $app->handle($request);
        $actual = $response->getContent();
        $expected = preg_replace('/\s/', '', '
            {
                "name": "method5"
            }');
        $this->assertEquals($expected, $actual);
        $this->assertEquals(
            'application/json',
            $response->headers->get('Content-Type'));
    }

    /**
     * @test
     */
    public function it_should_serialize_to_json_response_from_ext()
    {
        $request = Request::create('/r.json');
        $request->headers->set('Accept', '');
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->before(new ContentNegotiator($app['routes']));
        $app->after(new SerializationInspector($app['routes']));
        $app->get('/r.{_format}', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method5');
        $response = $app->handle($request);
        $actual = $response->getContent();
        $expected = preg_replace('/\s/', '', '
            {
                "name": "method5"
            }');
        $this->assertEquals($expected, $actual);
        $this->assertEquals(
            'application/json',
            $response->headers->get('Content-Type'));
    }


    /**
     * @test
     */
    public function it_should_serialize_to_xml_response()
    {
        $request = Request::create('/r');
        $request->headers->set('Accept', 'application/xml');
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->before(new ContentNegotiator($app['routes']));
        $app->after(new SerializationInspector($app['routes']));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method6');
        $response = $app->handle($request);
        $actual = preg_replace('/\s/', '', $response->getContent());
        $expected = preg_replace('/\s/', '',
            '<?xml version="1.0" encoding="utf-8"?>' .
            '<response><name>method6</name></response>');
        $this->assertEquals($expected, $actual);
        $this->assertEquals(
            'application/xml',
            $response->headers->get('Content-Type'));
    }
}
