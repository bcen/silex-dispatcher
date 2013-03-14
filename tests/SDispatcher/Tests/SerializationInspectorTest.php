<?php
namespace SDispatcher\Tests;

use SDispatcher\RouteOptions;
use SDispatcher\RouteOptionInspector;
use SDispatcher\ContentNegotiator;
use SDispatcher\SerializationInspector;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

class SerializationInspectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_serialize_to_json_response()
    {
        $request = Request::create('/r');
        $request->headers->set('Accept', 'application/json');
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->before(new ContentNegotiator($app));
        $app->after(new SerializationInspector($app));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method5');
        $response = $app->handle($request);
        $app->terminate($request, $response);
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
    public function should_serialize_to_xml_response()
    {
        $request = Request::create('/r');
        $request->headers->set('Accept', 'application/xml');
        $app = new Application();
        $app->before(new RouteOptionInspector($app['routes']));
        $app->before(new ContentNegotiator($app));
        $app->after(new SerializationInspector($app));
        $app->get('/r', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method6');
        $response = $app->handle($request);
        $app->terminate($request, $response);
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
