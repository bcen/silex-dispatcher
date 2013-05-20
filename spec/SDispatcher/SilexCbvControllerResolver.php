<?php

namespace spec\SDispatcher;

use PHPSpec2\Matcher\CustomMatchersProviderInterface;
use PHPSpec2\Matcher\InlineMatcher;
use PHPSpec2\ObjectBehavior;
use Prophecy\Prophet;
use SDispatcher\Common\RequiredServiceMetaProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class SilexCbvControllerResolver extends ObjectBehavior implements CustomMatchersProviderInterface
{
    /**
     * @var \Prophecy\Prophet
     */
    private $prophet;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $resolver;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $methodDispatcher;

    public function let()
    {
        $this->prophet = new Prophet();
        $this->resolver = $this->prophet->prophesize('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');
        $this->methodDispatcher = $this->prophet->prophesize('SDispatcher\ControllerMethodDispatcher');

        $this->beConstructedWith(
            $this->resolver->reveal(),
            $this->methodDispatcher->reveal());
    }

    public function letgo()
    {
        $this->prophet->checkPredictions();
    }

    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\SilexCbvControllerResolver');
        $this->shouldHaveType('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');
    }

    public function its_getController_should_return_closure_for_cbv_controller()
    {
        $request = Request::create('/');
        $request->attributes->set('_controller', 'spec\SDispatcher\CbvControllerWithoutDispatch');

        $this->getController($request)->shouldReturnClosure();
    }

    public function its_getController_should_resolve_controller_class_that_implemented_RequiredServiceMetaProviderInterface()
    {
        $request = Request::create('/');
        $request->attributes->set('_controller', 'spec\SDispatcher\CbvControllerWithDependency');

        $app = new Application();
        $app['my_obj'] = new \stdClass();

        $closure = $this->getController($request);
        $closure->__invoke(Request::create('/'), $app);
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

class CbvControllerWithDependency implements RequiredServiceMetaProviderInterface
{
    public static function getRequiredServices()
    {
        return array('my_obj');
    }

    public function __construct(\stdClass $obj)
    {
    }
}
