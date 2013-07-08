<?php

namespace spec\SDispatcher\Middleware;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ControllerConfiguredMiddlewareConverterSpec extends ObjectBehavior
{
    /**
     * @var \Prophecy\Prophet
     */
    private $prophet;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $routes;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $route;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $annotationResourceOption;

    /**
     * @var \Silex\Application
     */
    private $app;

    public function let()
    {
        $this->prophet = new Prophet();
        $this->routes = $this->prophet->prophesize('Symfony\\Component\\Routing\\RouteCollection');
        $this->route = $this->prophet->prophesize('Silex\\Route');
        $this->annotationResourceOption = $this->prophet->prophesize('SDispatcher\\Common\\AnnotationResourceOption');
        $this->app = new Application();

        $this->routes->get(Argument::any())->willReturn($this->route->reveal());

        $this->beConstructedWith($this->app, $this->routes->reveal(), $this->annotationResourceOption->reveal());
    }

    public function letgo()
    {
        $this->prophet->checkPredictions();
    }

    public function it_should_append_middlewares_to_route_if_controller_middleware_option_found()
    {
        $this->app['auth'] = function () { };
        $this->annotationResourceOption->getBeforeMiddlewares()->willReturn(array('auth'));
        $this->annotationResourceOption->getAfterMiddlewares()->willReturn(array('auth'));
        $this->annotationResourceOption->setTarget(Argument::any())->shouldBeCalled();
        $request = Request::create('/');
        $request->attributes->set('_controller', get_class());
        $this->route->before(Argument::any())->shouldBeCalled();
        $this->route->after(Argument::any())->shouldBeCalled();
        $this->__invoke($request);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('SDispatcher\Middleware\ControllerConfiguredMiddlewareConverter');
    }
}
