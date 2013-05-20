<?php

namespace spec\SDispatcher;

use PHPSpec2\Matcher\CustomMatchersProviderInterface;
use PHPSpec2\Matcher\InlineMatcher;
use PHPSpec2\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControllerMethodDispatcher extends ObjectBehavior implements CustomMatchersProviderInterface
{
    /**
     * @var \Prophecy\Prophet
     */
    private $prophet;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $resolver;

    public function let()
    {
        $this->prophet = new Prophet();
        $this->resolver = $this->prophet->prophesize('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');
        $this->resolver->getArguments(Argument::cetera())->willReturn(array());
    }

    public function letgo()
    {
        $this->prophet->checkPredictions();
    }

    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\ControllerMethodDispatcher');
    }

    public function its_dispatch_should_return_405_response_if_controller_is_not_dispatchable()
    {
        $controller = new \stdClass();
        $request = Request::create('/');
        $this->dispatch($request, $controller, $this->resolver->reveal())->shouldReturn405Response();
    }

    public function its_dispatch_should_invoke_target_controller_handleRequest_if_one_exists()
    {
        $controller = $this->prophet->prophesize('spec\\SDispatcher\\ControllerWithDispatchMethod');
        $controller->handleRequest(Argument::cetera())->shouldBeCalled();
        $request = Request::create('/');
        $this->dispatch($request, $controller->reveal(), $this->resolver->reveal());
    }

    public function its_dispatch_should_invoke_target_method_handler_if_one_exists()
    {
        $controller = $this->prophet->prophesize('spec\\SDispatcher\\ControllerWithGetMethodHandler');
        $controller->get(Argument::cetera())->shouldBeCalled();
        $request = Request::create('/');
        $this->dispatch($request, $controller->reveal(), $this->resolver->reveal());
    }

    public function its_dispatch_should_invoke_target_handleMissingMethod_at_last_resort()
    {
        $controller = $this->prophet->prophesize('spec\\SDispatcher\\ControllerWithMissingMethod');
        $controller->handleMissingMethod(Argument::cetera())->shouldBeCalled();
        $request = Request::create('/');
        $this->dispatch($request, $controller->reveal(), $this->resolver->reveal());
    }

    public static function getMatchers()
    {
        return array(
            new InlineMatcher('return405Response', function (Response $subject) {
                return $subject->getStatusCode() === 405;
            }),
        );
    }
}

class ControllerWithDispatchMethod
{
    public function handleRequest()
    {
    }
}

class ControllerWithGetMethodHandler
{
    public function get()
    {
        return 'get';
    }
}

class ControllerWithMissingMethod
{
    public function handleMissingMethod()
    {
        return 'handleMissingMethod';
    }
}
