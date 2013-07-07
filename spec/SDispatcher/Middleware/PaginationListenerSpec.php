<?php

namespace spec\SDispatcher\Middleware;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use SDispatcher\Common\InMemoryPaginator;
use SDispatcher\Common\RouteOptions;
use Symfony\Component\HttpFoundation\Request;

class PaginationListenerSpec extends ObjectBehavior
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
    private $event;

    public function let()
    {
        $this->prophet = new Prophet();
        $this->route = $this->prophet->prophesize('Symfony\\Component\\Routing\\Route');
        $this->routes = $this->prophet->prophesize('Symfony\\Component\\Routing\\RouteCollection');
        $this->event = $this->prophet->prophesize('Symfony\\Component\\HttpKernel\\Event\\GetResponseForControllerResultEvent');

        $this->route->getOption(RouteOptions::REST)->willReturn(true);
        $this->routes->get(Argument::any())->willReturn($this->route->reveal());

        $this->beConstructedWith($this->routes->reveal());
    }

    public function letgo()
    {
        $this->prophet->checkPredictions();
    }

    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\Middleware\PaginationListener');
    }

    public function it_should_shortcircuit_if_queryset_is_not_supported()
    {
        $request = Request::create('/');

        $this->event->getControllerResult()->willReturn(new \stdClass());
        $this->event->getRequest()->willReturn($request);

        $this->route->getOption(RouteOptions::PAGINATOR_CLASS)->wilLReturn(new InMemoryPaginator());

        $this->onKernelView($this->event->reveal())->shouldReturn(null);
    }

    public function it_should_shortcircuit_if_PaginatorClass_does_not_implement_the_correct_interface()
    {
        $request = Request::create('/');

        $this->event->getControllerResult()->willReturn(array());
        $this->event->getRequest()->willReturn($request);

        $this->route->getOption(RouteOptions::WILL_PAGINGATE)->willReturn(true);
        $this->route->getOption(RouteOptions::PAGINATOR_CLASS)->willReturn('stdClass');

        $this->onKernelView($this->event->reveal())->shouldReturn(null);
    }

    public function it_should_paginate_and_set_event_response_if_all_options_found()
    {
        $request = Request::create('/');

        $this->event->getControllerResult()->willReturn(array());
        $this->event->getRequest()->willReturn($request);
        $this->event->setResponse(Argument::any())->shouldBeCalled();

        $this->route->getOption(RouteOptions::WILL_PAGINGATE)->willReturn(true);
        $this->route->getOption(RouteOptions::PAGINATOR_CLASS)->willReturn('SDispatcher\\Common\InMemoryPaginator');
        $this->route->getOption(RouteOptions::PAGE_LIMIT)->willReturn(10);
        $this->route->getOption(RouteOptions::PAGINATED_META_CONTAINER_NAME)->willReturn('meta');
        $this->route->getOption(RouteOptions::PAGINATED_DATA_CONTAINER_NAME)->willReturn('objects');

        $this->onKernelView($this->event->reveal());
    }
}
