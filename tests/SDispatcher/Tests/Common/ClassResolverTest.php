<?php
namespace SDispatcher\Tests\Common;

use Twig_Environment;
use Twig_Loader_String;
use SDispatcher\DispatchingServiceProvider;
use SDispatcher\Common\ClassResolver;
use SDispatcher\TemplateEngine\TwigRendererAdapter;

class ClassResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function create_should_return_target_instance_with_expected_dependency()
    {
        $pimple['cookie'] = function() {
            return (object)array(
                'cookie_name' => '__abc__'
            );
        };
        $injector = new ClassResolver(array(
            'cookie' => (object)array(
                'cookie_name' => '__abc__'
            )
        ));

        $obj = $injector->create('SDispatcher\\Tests\\Common\\InjectMePlease');
        $this->assertEquals('__abc__', $obj->cookie->cookie_name);
    }

    /**
     * @test
     */
    public function create_should_return_target_instance_with_no_dependency()
    {
        $injector = new ClassResolver(array());
        $obj = $injector->create('stdClass');
        $this->assertInstanceOf('stdClass', $obj);
    }

    /**
     * @test
     * @expectedException \SDispatcher\Common\Exception\NoDependencyFoundException
     */
    public function create_should_throw_NoDependencyFoundException_with_non_existent_dependency()
    {
        $injector = new ClassResolver(array());
        $injector->create('SDispatcher\\Tests\\Common\\InjectMePlease');
    }

    /**
     * @test
     */
    public function onFinish_should_be_called_after_object_creation()
    {
        $resolver = new ClassResolver(array(
            DispatchingServiceProvider::TEMPLATE_RENDERER => new TwigRendererAdapter(
                new Twig_Environment(new Twig_Loader_String())
            )
        ));
        $resolver->onFinish(function(array $containers, $object) {
            if ($object instanceof TemplateRendererAwareSpy) {
                foreach ($containers as $container) {
                    if (isset($container[DispatchingServiceProvider::TEMPLATE_RENDERER])) {
                        $object->setRenderer($container[DispatchingServiceProvider::TEMPLATE_RENDERER]);
                    }
                }
            }
        });
        $obj = $resolver->create('SDispatcher\\Tests\\Common\\TemplateRendererAwareSpy');
        $this->assertTrue($obj->set);
    }

    /**
     * @test
     */
    public function create_should_be_able_to_lookup_in_array_of_containers()
    {
        $resolver = new ClassResolver(array(), array(
            'cookie' => new \stdClass()
        ));
        $object = $resolver->create('SDispatcher\\Tests\\Common\\InjectMePlease');
    }

    /**
     * @test
     */
    public function create_should_able_lookup_from_type_hint()
    {
        $resolver = new ClassResolver(array(), array(
            'stdClass' => new \stdClass()
        ));
        $object = $resolver->create('SDispatcher\\Tests\\Common\\InjectMePlease');
    }

    /**
     * @test
     */
    public function create_should_prioritize_name_over_type_hint()
    {
        $cookie = new \stdClass();
        $cookie->name = 'hey';
        $resolver = new ClassResolver(
            array('stdClass' => new \stdClass()),
            array('cookie' => $cookie)
        );
        $object = $resolver->create('SDispatcher\\Tests\\Common\\InjectMePlease');
        $this->assertEquals('hey', $object->cookie->name);
    }
}

class InjectMePlease
{
    public $cookie;

    public function __construct(\stdClass $cookie)
    {
        $this->cookie = $cookie;
    }
}

class TemplateRendererAwareSpy implements \SDispatcher\TemplateEngine\TemplateRendererAwareInterface
{
    public $set = false;

    public function getRenderer()
    {
    }

    public function setRenderer(\SDispatcher\TemplateEngine\TemplateRendererInterface $renderer)
    {
        $this->set = true;
    }
}
