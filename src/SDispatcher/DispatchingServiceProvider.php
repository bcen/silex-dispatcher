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
        $app[DispatchingServiceProvider::CLASS_RESOLVER] = $app->share(
            function($container) {
                return new ClassResolver(
                    $container,
                    array('Silex\\Application' => $container)
                );
            }
        );

        // alias of DispatchingServiceProvider::CLASS_RESOLVER
        $app['SDispatcher\Common\ClassResolver'] = function($c) {
            return $c[DispatchingServiceProvider::CLASS_RESOLVER];
        };

        $app[DispatchingServiceProvider::CONTROLLER_FACTORY] = $app->share(
            function($container) {
                return new ControllerFactory(
                    $container[DispatchingServiceProvider::CLASS_RESOLVER]
                );
            }
        );

        // alias of DispatchingServiceProvider::CONTROLLER_FACTORY
        $app['SDispatcher\\ControllerFactory'] = function($c) {
            return $c[DispatchingServiceProvider::CONTROLLER_FACTORY];
        };
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        if (isset($app['twig']) && $app['twig'] instanceof Twig_Environment) {
            $app[DispatchingServiceProvider::TEMPLATE_RENDERER] = $app->share(
                function($container) {
                    return new TwigRendererAdapter($container['twig']);
                }
            );

            $app['SDispatcher\\TemplateEngine\\TwigRendererAdapter'] =
                function($c) {
                    return $c[DispatchingServiceProvider::TEMPLATE_RENDERER];
                };
        }
    }
}
