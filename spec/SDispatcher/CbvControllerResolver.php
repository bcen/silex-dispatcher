<?php

namespace spec\SDispatcher;

use PHPSpec2\Matcher\CustomMatchersProviderInterface;
use PHPSpec2\Matcher\InlineMatcher;
use PHPSpec2\ObjectBehavior;
use Prophecy\Prophet;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class CbvControllerResolver extends ObjectBehavior implements CustomMatchersProviderInterface
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

        $this->beConstructedWith($this->resolver->reveal());
    }

    public function letgo()
    {
        $this->prophet->checkPredictions();
    }

    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\CbvControllerResolver');
        $this->shouldHaveType('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');
    }

    public function its_getController_should_return_closure_for_cbv_controller()
    {
        $request = Request::create('/');
        $request->attributes->set('_controller', 'spec\SDispatcher\CbvControllerWithoutDispatch');

        $this->getController($request)->shouldReturnClosure();
    }

    public function its_returned_closure_should_return_405_response_for_cbv_controller_without_dispatch()
    {
        $request = Request::create('/');
        $request->attributes->set('_controller', 'spec\SDispatcher\CbvControllerWithoutDispatch');

        $closure = $this->getController($request);
        $response = call_user_func($closure, Request::create('/'), new Application());
        $response = $response->getWrappedSubject();
        if ($response->getStatusCode() !== 405) {
            throw new \LogicException('Should return 405 when class does not have dispatch method');
        }
    }

    public function its_returned_closure_should_return_corrected_method_response_for_controller_with_correct_method_handler()
    {
        $request = Request::create('/');
        $request->attributes->set('_controller', 'spec\SDispatcher\CbvControllerForGet');

        $closure = $this->getController($request);
        $response = call_user_func($closure, Request::create('/'), new Application());
        $response->shouldReturn('get');
    }

    public function its_returned_closure_should_return_corrected_method_response_for_controller_with_handleRequest_method()
    {
        $request = Request::create('/');
        $request->attributes->set('_controller', 'spec\SDispatcher\CbvControllerForHandleRequest');

        $closure = $this->getController($request);
        $response = call_user_func($closure, Request::create('/'), new Application());
        $response->shouldReturn('handleRequest');
    }

    public static function getMatchers()
    {
        return array(
            new InlineMatcher('returnClosure', function ($subject) {
                return ($subject instanceof \Closure);
            }),
        );
    }
}

class CbvControllerWithoutDispatch
{
}

class CbvControllerForGet
{
    public function get()
    {
        return 'get';
    }
}

class CbvControllerForHandleRequest
{
    public function handleRequest()
    {
        return 'handleRequest';
    }
}
