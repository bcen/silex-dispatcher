<?php
namespace SDispatcher;

use Twig_Environment;
use SDispatcher\TemplateEngine\TwigRendererAdapter;
use SDispatcher\ControllerFactory;
use SDispatcher\Common\ClassResolver;
use Silex\Application;
use Silex\ServiceProviderInterface;

class DispatchingServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    const CLASS_RESOLVER = 'sdispatcher.class_resolver';

    /**
     * @var string
     */
    const CONTROLLER_FACTORY = 'sdispatcher.controller_factory';

    /**
     * @var string
     */
    const TEMPLATE_RENDERER = 'sdispatcher.template_renderer';

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app[DispatchingServiceProvider::CLASS_RESOLVER] = $app->share(function($container) {
            return new ClassResolver($container, array('silex' => $container));
        });

        $app[DispatchingServiceProvider::CONTROLLER_FACTORY] = $app->share(function($container) {
            return new ControllerFactory($container[DispatchingServiceProvider::CLASS_RESOLVER]);
        });

        if (isset($app['twig']) && $app['twig'] instanceof Twig_Environment) {
            $app[DispatchingServiceProvider::TEMPLATE_RENDERER] = $app->share(function($container) {
                return new TwigRendererAdapter($container['twig']);
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
