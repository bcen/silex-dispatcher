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

    public function it_should_replace_request_param_if_json_found()
    {
        $request = Request::create(
            '/',
            'POST',
            array(),
            array(),
            array(),
            array(),
            '{"message":"hello world"}');
        $this->decoderProvider->supports('json')->willReturn(true);
        $this->decoderProvider->getDecoder('json')->willReturn(new JsonDecoder());
        $request->headers->set('Content-Type', 'application/json');
        $this->__invoke($request);

        if ($request->request->get('message') !== 'hello world') {
            throw new \Exception();
        }
    }

    public function it_should_replace_request_param_if_xml_found()
    {
        $request = Request::create(
            '/',
            'POST',
            array(),
            array(),
            array(),
            array(),
            '<xml><message>hello world</message></xml>');
        $this->decoderProvider->supports('xml')->willReturn(true);
        $this->decoderProvider->getDecoder('xml')->willReturn(new XmlDecoder());
        $request->headers->set('Content-Type', 'application/xml');
        $this->__invoke($request);

        if ($request->request->get('message') !== 'hello world') {
            throw new \Exception();
        }
    }
}
