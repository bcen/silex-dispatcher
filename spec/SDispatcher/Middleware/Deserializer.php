<?php

namespace spec\SDispatcher\Middleware;

use FOS\Rest\Decoder\JsonDecoder;
use FOS\Rest\Decoder\XmlDecoder;
use PHPSpec2\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Request;

class Deserializer extends ObjectBehavior
{
    /**
     * @var \Prophecy\Prophet
     */
    private $prophet;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $decoderProvider;

    public function let()
    {
        $this->prophet = new Prophet();
        $this->decoderProvider = $this->prophet->prophesize('SDispatcher\\Common\\FOSDecoderProvider');

        $this->beConstructedWith($this->decoderProvider->reveal());
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
