<?php
namespace SDispatcher\Tests;

use SDispatcher\DispatchingServiceProvider;
use Silex\Application;

class DispatchingServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_register_ClassResolver_and_ControllerFactory()
    {
        $app = new Application();
        $app->register(new \Silex\Provider\TwigServiceProvider());
        $app->register(new DispatchingServiceProvider());
        $this->assertInstanceOf('SDispatcher\\Common\\ClassResolver', $app[DispatchingServiceProvider::CLASS_RESOLVER]);
        $this->assertInstanceOf('SDispatcher\\ControllerFactory', $app[DispatchingServiceProvider::CONTROLLER_FACTORY]);
        $this->assertInstanceof('SDispatcher\\TemplateEngine\\TwigRendererAdapter', $app[DispatchingServiceProvider::TEMPLATE_RENDERER]);
    }
}
