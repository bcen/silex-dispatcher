<?php

namespace spec\SDispatcher\Common;

use PhpSpec\ObjectBehavior;

class DeclarativeResourceOptionSpec extends ObjectBehavior
{
    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\Common\DeclarativeResourceOption');
    }

    public function it_should_return_default_option_if_everything_failed()
    {
        $this->getSupportedFormats()->shouldReturn(array('json'));
        $this->getDefaultFormat()->shouldReturn('json');
        $this->getPaginatorClass()->shouldReturn('SDispatcher\\Common\\InMemoryPaginator');
        $this->getPageLimit()->shouldReturn(20);
        $this->getAllowedMethods()->shouldReturn(array('GET'));
        $this->getResourceIdentifier()->shouldReturn('id');
        $this->getPaginatedDataContainerName()->shouldReturn('objects');
        $this->getPaginatedMetaContainerName()->shouldReturn('meta');
        $this->getRequiredServices()->shouldReturn(array());
    }

    public function it_should_read_option_from_obj_static_variable()
    {
        $this->setTarget(new DeclarativeOptionClass());
        $this->getSupportedFormats()->shouldReturn(array('yml', 'text'));
    }

    public function it_should_read_option_from_obj_variable()
    {
        $this->setTarget(new DeclarativeOptionClass());
        $this->getPageLimit()->shouldReturn(1);
    }

    public function it_should_read_option_from_class_static_variable()
    {
        $this->setTarget('spec\\SDispatcher\\Common\\DeclarativeOptionClass');
        $this->getSupportedFormats()->shouldReturn(array('yml', 'text'));
    }
}

class DeclarativeOptionClass
{
    private static $supportedFormats = array('yml', 'text');
    private $pageLimit = 1;
}
