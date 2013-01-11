<?php
namespace SDispatcher\Tests;

use SDispatcher\ControllerFactory;
use SDispatcher\Exception\DispatchingErrorException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $controllerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $silexMock;

    public function setUp()
    {
        $this->resolverMock = $this->getMock('SDispatcher\\Common\\ClassResolver');
        $this->controllerMock = $this->getMock('SDispatcher\\DispatchableController');
        $this->silexMock = $this->getMock('Silex\\Application');

        $this->controllerMock->expects($this->any())
            ->method('doDispatch')
            ->will($this->returnValue(new Response()));
        $this->resolverMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->controllerMock));
    }

    /**
     * @test
     */
    public function ControllerFactory_instance_should_return_true_for_is_callable()
    {
        $controller = new ControllerFactory($this->resolverMock);
        $this->assertTrue(is_callable($controller));
    }

    /**
     * @test
     */
    public function __invoke_should_return_anonymous_function()
    {
        $controller = new ControllerFactory($this->resolverMock);
        $func = $controller('\stdClass');
        $this->assertTrue($func instanceof \Closure);
    }

    /**
     * @test
     */
    public function anonymous_function_should_return_Response_instance()
    {
        $controller = new ControllerFactory($this->resolverMock);
        $func = $controller('SDispatcher\\Whatever');
        $this->assertTrue(
            $func($this->silexMock, Request::create('/')) instanceof Response
        );
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function anonymous_function_should_convert_all_DispatchingErrorException_to_HttpException()
    {
        $this->controllerMock->expects($this->once())
            ->method('doDispatch')
            ->will($this->throwException(new DispatchingErrorException()));
        $controller = new ControllerFactory($this->resolverMock);
        $func = $controller('SDispatcher\\Whatever');
        $func($this->silexMock, Request::create('/'));
    }
}
