<?php

namespace spec\SDispatcher\Middleware;

use FOS\Rest\Decoder\JsonDecoder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use SDispatcher\Common\RouteOptions;
use Symfony\Component\HttpFoundation\Request;

class DeserializerSpec extends ObjectBehavior
{
    /**
     * @var \Prophecy\Prophet
     */
    private $prophet;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $decoderProvider;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $route;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $routes;

    public function let()
    {
        $this->prophet = new Prophet();
        $this->route = $this->prophet->prophesize('Symfony\\Component\\Routing\\Route');
        $this->routes = $this->prophet->prophesize('Symfony\\Component\\Routing\\RouteCollection');
        $this->decoderProvider = $this->prophet->prophesize('SDispatcher\\Common\\FOSDecoderProvider');

        $this->route->getOption(RouteOptions::REST)->willReturn(true);
        $this->routes->get(Argument::any())->willReturn($this->route->reveal());

        $this->beConstructedWith(
            $this->routes->reveal(),
            $this->decoderProvider->reveal());
    }

    public function letgo()
    {
        $this->prophet->checkPredictions();
    }

    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\Middleware\Deserializer');
    }

    public function it_should_replace_request_param_with_decoded_content_if_format_is_supported()
    {
        $requestParam = $this->prophet->prophesize('Symfony\\Component\\HttpFoundation\\ParameterBag');
        $request = Request::create('/');
        $request->request = $requestParam->reveal();
        $requestParam->replace(Argument::any())->shouldBeCalled();
        $this->decoderProvider->supports(Argument::any())->willReturn(true);
        $this->decoderProvider->getDecoder(Argument::any())->willReturn(new JsonDecoder());
        $this->__invoke($request);
    }

    public function it_should_not_replace_request_param_if_format_is_not_supported()
    {
        $requestParam = $this->prophet->prophesize('Symfony\\Component\\HttpFoundation\\ParameterBag');
        $request = Request::create('/');
        $request->request = $requestParam->reveal();
        $requestParam->replace(Argument::any())->shouldNotBeCalled();
        $this->decoderProvider->supports(Argument::any())->willReturn(false);
        $this->__invoke($request);
    }
}
