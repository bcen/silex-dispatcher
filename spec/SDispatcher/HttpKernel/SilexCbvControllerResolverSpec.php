<?php

namespace spec\SDispatcher\HttpKernel;

use PhpSpec\ObjectBehavior;
use SDispatcher\Common\RequiredServiceMetaProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class SilexCbvControllerResolverSpec extends ObjectBehavior
{
    public function let()
    {
        $app = new Application();
        $app['my_obj'] = new \stdClass();
        $this->beConstructedWith($app, $app['resolver']);
    }

    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\HttpKernel\SilexCbvControllerResolver');
        $this->shouldHaveType('Silex\ControllerResolver');
    }

    public function its_getController_should_resolve_controller_class_string_into_array_form()
    {
        $request = Request::create('/');
        $request->attributes->set('_controller', 'spec\SDispatcher\HttpKernel\ControllerStub');
        $this->getController($request)->shouldReturnArrayFormat();
    }

    public function its_getController_should_resolve_normal_controller_format()
    {
        $request = Request::create('/');
        $request->attributes->set('_controller', 'spec\SDispatcher\HttpKernel\ControllerStub::get');
        $this->getController($request)->shouldReturnArrayFormat();
    }

    public function its_getController_should_resolve_dependency()
    {
        $request = Request::create('/');
        $request->attributes->set('_controller', 'spec\SDispatcher\HttpKernel\ControllerNeedDependencyStub');
        $this->getController($request);
    }

    public function getMatchers()
    {
        return array(
            'returnArrayFormat' => function ($subject) {
                return is_array($subject) && count($subject) >= 2;
            },
        );
    }
}

class ControllerStub
{
    public function get()
    {
        return 'get';
    }
}

class ControllerNeedDependencyStub implements RequiredServiceMetaProviderInterface
{
    public function __construct(\stdClass $obj)
    {
    }

    public function get()
    {
        return 'get';
    }

    public static function getRequiredServices()
    {
        return array('my_obj');
    }
}
