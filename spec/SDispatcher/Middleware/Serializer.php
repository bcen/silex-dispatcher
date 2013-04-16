<?php

namespace spec\SDispatcher\Middleware;

use PHPSpec2\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Request;

class Serializer extends ObjectBehavior
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
    private $response;

    public function let()
    {
        $this->prophet = new Prophet();
        $this->route = $this->prophet->prophesize('Symfony\\Component\\Routing\\Route');
        $this->routes = $this->prophet->prophesize('Symfony\\Component\\Routing\\RouteCollection');
        $this->response = $this->prophet->prophesize('SDispatcher\\DataResponse');

        $this->routes->get(Argument::any())->willReturn($this->route->reveal());

        $this->beConstructedWith($this->routes->reveal());
    }

    public function letgo()
    {
        $this->prophet->checkPredictions();
    }

    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\Middleware\Serializer');
    }

    public function it_should_return_406_response_if_no_accepted_format_found()
    {
        $this->route->getOption(Argument::cetera())->willReturn(null);
        $this->response->setContent('')->shouldBeCalled();
        $this->response->setStatusCode(406)->shouldBeCalled();
        $request = Request::create('/');
        $this->__invoke($request, $this->response->reveal());
    }
}
