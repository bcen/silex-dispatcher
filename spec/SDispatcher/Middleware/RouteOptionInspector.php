<?php

namespace spec\SDispatcher\Middleware;

use PHPSpec2\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use SDispatcher\Common\RouteOptions;
use Symfony\Component\HttpFoundation\Request;

class RouteOptionInspector extends ObjectBehavior
{
    /**
     * @var \Prophecy\Prophet
     */
    private $prophet;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $route;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $routes;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $annotationResourceOption;

    public function let()
    {
        $this->prophet = new Prophet();
        $this->route = $this->prophet->prophesize('Symfony\\Component\\Routing\\Route');
        $this->routes = $this->prophet->prophesize('Symfony\\Component\\Routing\\RouteCollection');
        $this->annotationResourceOption = $this->prophet->prophesize('SDispatcher\\Common\\AnnotationResourceOption');

        $this->route->getOption(RouteOptions::REST)->willReturn(true);
        $this->routes->get(Argument::any())->willReturn($this->route->reveal());

        $this->beConstructedWith(
            $this->routes->reveal(),
            $this->annotationResourceOption->reveal());
    }

    public function letgo()
    {
        $this->prophet->checkPredictions();
    }

    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\Middleware\RouteOptionInspector');
    }

    public function it_should_not_set_route_option_if_no_controller_found_from_request()
    {
        $request = Request::create('/');
        $this->route->addOptions(Argument::any())->shouldNotBeCalled();
        $this->__invoke($request);
    }

    public function it_should_set_route_option_if_controller_found()
    {
        $request = Request::create('/');
        $request->attributes->set('_controller', 'spec\SDispatcher\Middleware\RouteOptionInspector');
        $this->route->addOptions(Argument::any())->shouldBeCalled();

        $this->__invoke($request);
    }
}
