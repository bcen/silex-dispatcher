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

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $encoder;

    public function let()
    {
        $this->prophet = new Prophet();
        $this->route = $this->prophet->prophesize('Symfony\\Component\\Routing\\Route');
        $this->routes = $this->prophet->prophesize('Symfony\\Component\\Routing\\RouteCollection');
        $this->response = $this->prophet->prophesize('SDispatcher\\DataResponse');
        $this->encoder = $this->prophet->prophesize('Symfony\\Component\\Serializer\\Encoder\\EncoderInterface');

        $this->routes->get(Argument::any())->willReturn($this->route->reveal());

        $this->beConstructedWith(
            $this->routes->reveal(),
            $this->encoder->reveal());
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

    public function it_should_do_nothing_if_no_format_is_supported()
    {
        $this->route->getOption(Argument::any())->willReturn('lol');
        $this->response->setContent(Argument::any())->shouldNotBeCalled();
        $request = Request::create('/');
        $this->__invoke($request, $this->response->reveal())->shouldReturn(null);
    }

    public function it_should_update_response_if_xml_is_supported()
    {
        $headers = $this->prophet->prophesize('Symfony\\Component\\HttpFoundation\\ParameterBag');
        $headers->set('Content-Type', 'text/xml')->shouldBeCalled();
        $this->response->headers = $headers->reveal();
        $this->route->getOption(Argument::any())->willReturn('xml');
        $this->encoder->supportsEncoding('xml')->willReturn(true);
        $this->encoder->encode(Argument::cetera())->willReturn('');
        $this->response->getContent()->willReturn('');
        $this->response->setContent(Argument::any())->shouldBeCalled();
        $request = Request::create('/');
        $this->__invoke($request, $this->response->reveal())->shouldReturn(null);
    }

    public function it_should_update_response_if_json_is_supported()
    {
        $headers = $this->prophet->prophesize('Symfony\\Component\\HttpFoundation\\ParameterBag');
        $headers->set('Content-Type', 'application/json')->shouldBeCalled();
        $this->response->headers = $headers->reveal();
        $this->route->getOption(Argument::any())->willReturn('json');
        $this->encoder->supportsEncoding('json')->willReturn(true);
        $this->encoder->encode(Argument::cetera())->willReturn('');
        $this->response->getContent()->willReturn('');
        $this->response->setContent(Argument::any())->shouldBeCalled();
        $request = Request::create('/');
        $this->__invoke($request, $this->response->reveal())->shouldReturn(null);
    }

}
