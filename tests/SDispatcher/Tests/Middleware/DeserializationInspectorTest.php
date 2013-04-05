<?php
namespace SDispatcher\Tests\Middleware;

use SDispatcher\Middleware\DeserializationInspector;
use Silex\Application;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class DeserializationInspectorTest extends AbstractMiddlewareTestCaseHelper
{
    /**
     * @test
     */
    public function it_should_deserialize_on_content_type()
    {
        $request = Request::create(
            '/',
            'GET',
            array(),
            array(),
            array(),
            array(),
            '{"name": "someone"}');
        $request->headers->set('Content-Type', 'application/json');
        $app = new Application();
        $app->before(new DeserializationInspector());
        $app->get('/', 'SDispatcher\\Tests\\Fixture\\AnnotateMePlease::method1');
        $app->handle($request);

        $this->assertEquals('someone', $request->request->get('name'));
        $this->assertEquals(
            array('name' => 'someone'),
            $request->request->all());
    }

    /**
     * @test
     */
    public function it_should_deserialize_to_empty_array_if_no_content_body_found()
    {
        $request = Request::create(
            '/',
            'GET',
            array(),
            array(),
            array(),
            array(),
            null);
        $request->headers->set('Content-Type', 'application/json');
        $middleware = new DeserializationInspector();

        $middleware($request);

        $this->assertEquals(array(), $request->request->all());
    }
}
