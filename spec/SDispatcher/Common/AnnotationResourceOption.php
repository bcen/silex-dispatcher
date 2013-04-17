<?php

namespace spec\SDispatcher\Common;

use PHPSpec2\ObjectBehavior;
use SDispatcher\Common\Annotation as REST;

/**
 * @REST\SupportedFormats("xml")
 */
class AnnotationResourceOption extends ObjectBehavior
{
    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\Common\AnnotationResourceOption');
    }

    public function its_setTarget_should_throw_exception_if_args_are_invalid()
    {
        $this->shouldThrow('\ReflectionException')->duringSetTarget('lol', 'lol');
    }

    public function it_should_return_default_option_if_everything_failed()
    {
        $this->getSupportedFormats()->shouldReturn(array('json'));
        $this->getDefaultFormat()->shouldReturn('json');
        $this->willPaginate()->shouldReturn(false);
        $this->getPaginatorClass()->shouldReturn('SDispatcher\\Common\\InMemoryPaginator');
        $this->getPageLimit()->shouldReturn(20);
        $this->getAllowedMethods()->shouldReturn(array('GET'));
        $this->getResourceIdentifier()->shouldReturn('id');
        $this->getPaginatedDataContainerName()->shouldReturn('objects');
        $this->getPaginatedMetaContainerName()->shouldReturn('meta');
    }

    public function it_should_read_annotation_from_class()
    {
        $this->setTarget('spec\\SDispatcher\\Common\AnnotationResourceOption', 'it_should_be_initializable');
        $this->getSupportedFormats()->shouldReturn(array('xml'));
    }

    public function it_reads_through_the_class_hierarchy()
    {
        $this->setTarget('spec\\SDispatcher\\Common\\DummyClass', 'dummyMethod');
        $this->getSupportedFormats()->shouldReturn(array('xml'));
    }

    /**
     * @REST\SupportedFormats("html")
     */
    public function it_should_read_annotation_from_method_over_class()
    {
        $this->setTarget('spec\\SDispatcher\\Common\AnnotationResourceOption', 'it_should_read_annotation_from_method_over_class');
        $this->getSupportedFormats()->shouldReturn(array('html'));
    }
}

class DummyClass extends AnnotationResourceOption
{
    public function dummyMethod()
    {
    }
}
