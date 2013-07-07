<?php

namespace spec\SDispatcher\Middleware;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use SDispatcher\Common\RouteOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentNegotiatorSpec extends ObjectBehavior
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
    private $formatNegotiator;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $route;

    public function let()
    {
        $this->prophet = new Prophet();
        $this->routes = $this->prophet->prophesize('Symfony\\Component\\Routing\\RouteCollection');
        $this->formatNegotiator = $this->prophet->prophesize('FOS\\Rest\\Util\\FormatNegotiator');
        $this->route = $this->prophet->prophesize('Symfony\\Component\\Routing\\Route');

        $this->route->getOption(RouteOptions::REST)->willReturn(true);
        $this->routes->get(Argument::any())->willReturn($this->route->reveal());

        $this->beConstructedWith(
            $this->routes->reveal(),
            $this->formatNegotiator->reveal());
    }

    public function letgo()
    {
        $this->prophet->checkPredictions();
    }

    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\Middleware\ContentNegotiator');
    }

    public function it_should_convert_format_from_query_string_to_request_attribute()
    {
        $this->route->getOption(RouteOptions::SUPPORTED_FORMATS)->willReturn(array('json', 'xml'));
        $request = Request::create('/?format=my_format');
        $this->__invoke($request);
        if ($request->attributes->get('_format') !== 'my_format') {
            throw new \Exception();
        }
    }

    public function it_should_set_accepted_format_and_return_null_as_success_if_best_format_found()
    {
        $this->formatNegotiator->getBestFormat(Argument::cetera())->willReturn('json');
        $this->route->getOption(RouteOptions::SUPPORTED_FORMATS)->willReturn(array('json', 'xml'));
        $this->route->setOption(RouteOptions::ACCEPTED_FORMAT, 'json')->shouldBeCalled();
        $request = Request::create('/?format=json');
        $this->__invoke($request)->shouldReturn(null);
    }

    public function it_should_return_406_response_if_no_best_format_found()
    {
        $this->formatNegotiator->getBestFormat(Argument::cetera())->willReturn(null);
        $this->route->getOption(Argument::any())->shouldBeCalled();
        $request = Request::create('/?format=json');
        $this->__invoke($request)->shouldReturn406Response();
    }

    public function getMatchers()
    {
        return array(
            'return406Response' => function (Response $subject) {
                return $subject->getStatusCode() === 406;
            },
        );
    }
}
