<?php
namespace SDispatcher\Tests;

use SDispatcher\DispatchableController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DispatchableControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function doDispatch_should_return_empty_response()
    {
        $controller = new DummyController();
        $response = $controller->doDispatch(Request::create('/'));
        $this->assertInstanceOf(
            'Symfony\\Component\\HttpFoundation\\Response',
            $response
        );
        $this->assertEquals('', $response->getContent());
    }

    /**
     * @test
     * @expectedException \SDispatcher\Exception\DispatchingErrorException
     */
    public function doDispatch_should_throw_DispatchingErrorException_for_invalid_number_of_arguments_in_request_method_handler()
    {
        $controller = new DummyController();
        $controller->doDispatch(
            new Request(array(), array(), array('id' => 1)),
            array('id')
        );
    }

    /**
     * @test
     */
    public function doDispatch_should_invoke_the_request_method_handler()
    {
        $controller = new DummyControllerSpy();
        $controller->doDispatch(Request::create('http://dev.local/'));
        $this->assertTrue($controller->called);

        $controller = new DummyControllerSpy();
        $controller->doDispatch(Request::create('http://dev.local/', 'POST'));
        $this->assertTrue($controller->called);

        $controller = new DummyControllerSpy();
        $controller->doDispatch(Request::create('http://dev.local/', 'PUT'));
        $this->assertTrue($controller->called);

        $controller = new DummyControllerSpy();
        $controller->doDispatch(Request::create('http://dev.local/', 'DELETE'));
        $this->assertTrue($controller->called);

        $controller = new DummyControllerSpy();
        $controller->doDispatch(Request::create('http://dev.local/', 'X_GET_POST'));
        $this->assertTrue($controller->called);
    }

    /**
     * @test
     * @expectedException \SDispatcher\Exception\DispatchingErrorException
     */
    public function doDispatch_should_throw_DispatchingErrorException_when_no_request_method_handler()
    {
        $controller = new DummyController();
        $controller->doDispatch(Request::create('/', 'POST'));
    }

    /**
     * @test
     */
    public function doDispatch_should_render_response_with_renderView_from_template_property_by_default()
    {
        $controller = new DummyController();
        $controller->template = 'Hello World';
        $response = $controller->doDispatch(Request::create('/'));
        $this->assertEquals(
            $controller->template,
            $response->getContent()
        );
    }
}

class DummyController extends DispatchableController
{
}

class DummyControllerSpy extends \SDispatcher\DispatchableController
{
    public $called = false;

    public function get(Request $request)
    {
        $this->called = true;
        return new Response();
    }

    public function post(Request $request)
    {
        $this->called = true;
        return new Response();
    }

    public function put(Request $request)
    {
        $this->called = true;
        return new Response();
    }

    public function delete(Request $request)
    {
        $this->called = true;
        return new Response();
    }

    public function x_get_post(Request $request)
    {
        $this->called = true;
        return new Response();
    }
}
