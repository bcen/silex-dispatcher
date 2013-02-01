<?php
namespace SDispatcher\Tests\Common;

use SDispatcher\Common\DeclarativeResourceOption;

class DeclarativeResourceOptionTest extends \PHPUnit_Framework_TestCase
{
    private $test_supportedFormats = array(
        'application/test_prefix'
    );

    /**
     * @test
     */
    public function should_able_to_read_from_property()
    {
        $options = new DeclarativeResourceOption(new Options());

        $this->assertEquals(
            array('application/whatever'),
            $options->getSupportedFormats()
        );
    }

    /**
     * @test
     */
    public function should_able_to_read_from_method()
    {
        $options = new DeclarativeResourceOption(new Options());

        $this->assertEquals(
            15,
            $options->getPageLimit()
        );
    }

    /**
     * @test
     */
    public function should_able_to_set_prefix()
    {
        $options = new DeclarativeResourceOption($this, 'test_');
        $this->assertEquals(
            array('application/test_prefix'),
            $options->getSupportedFormats()
        );
    }

    /**
     * @test
     */
    public function property_should_have_more_priority_than_method()
    {
        $options = new DeclarativeResourceOption(new DummyOptions());

        $this->assertEquals(
            array('application/first'),
            $options->getSupportedFormats()
        );
    }

    /**
     * @test
     */
    public function should_able_to_read_from_static_property()
    {
        $options = new DeclarativeResourceOption(new StaticOptions());

        $this->assertEquals(
            1100,
            $options->getPageLimit()
        );
    }

    /**
     * @test
     */
    public function should_able_to_set_static_property()
    {
        $options = new DeclarativeResourceOption(new StaticOptions());
        $options->setPageLimit(1234);
        $this->assertEquals(
            1234,
            $options->getPageLimit()
        );
    }

    /**
     * @test
     */
    public function should_able_to_set_property()
    {
        $options = new DeclarativeResourceOption(new Options());
        $options->setSupportedFormats(array('text/html'));

        $this->assertEquals(
            array('text/html'),
            $options->getSupportedFormats()
        );
    }

    /**
     * @test
     */
    public function should_able_to_set_option_and_get()
    {
        $option = new DeclarativeResourceOption(new StaticOptions());
        $option->setAllowedMethods(array('DO'));
        $this->assertEquals(array('DO'), $option->getAllowedMethods());
    }

    /**
     * @test
     */
    public function cache_should_have_priority()
    {
        $option = new DeclarativeResourceOption(new StaticOptions());
        $this->assertEquals(1100, $option->getPageLimit());
        $option->setPageLimit(110);
        $this->assertEquals(110, $option->getPageLimit());
    }
}

class Options
{
    private $supportedFormats = array(
        'application/whatever'
    );

    private function pageLimit()
    {
        return 15;
    }
}

class DummyOptions
{
    private $supportedFormats = array(
        'application/first'
    );

    private function supportedFormats()
    {
        return 'application/second';
    }
}

class StaticOptions
{
    private static $pageLimit = 1100;
}
