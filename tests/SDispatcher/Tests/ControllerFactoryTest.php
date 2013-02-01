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
     */
    public function makeRoute_should_match_route_pattern_to_controller_class_with_4_default_methods()
    {
        $routeMock = $this->getMock('Silex\\Route');
        $routeMock->expects($this->once())
            ->method('method')
            ->with($this->equalTo('GET|POST|PUT|DELETE'));
        $this->silexMock->expects($this->once())
            ->method('match')
            ->with($this->equalTo('/r/{id}/{id2}'))
            ->will($this->returnValue($routeMock));
        $controller = $this->getMock('SDispatcher\\ControllerFactory', array('createClosure'), array($this->resolverMock));
        $controller->expects($this->once())
            ->method('createClosure')
            ->with($this->equalTo('Arcphss\\Delivery\\Controller\\RootController'), $this->equalTo(array('id', 'id2')));

        $controller->makeRoute($this->silexMock, '/r/{id}/{id2}', 'Arcphss\\Delivery\\Controller\\RootController');
    }
}
