<?php
namespace SDispatcher;

use FOS\Rest\Util\FormatNegotiator;
use SDispatcher\Common\DefaultXmlEncoder;
use SDispatcher\Common\FOSDecoderProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Encoder\ChainEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Registers services into the container.
 */
class SDispatcherServiceProvider implements ServiceProviderInterface
{
    public function getDefaultParameters()
    {
        return array(
            'sdispatcher.global_middleware'         => false,
            'sdispatcher.cbv_resolver.class'        => 'SDispatcher\\HttpKernel\\SilexCbvControllerResolver',
            'sdispatcher.resource_option.class'     => 'SDispatcher\\Common\\AnnotationResourceOption',
            'sdispatcher.option_inspector.class'    => 'SDispatcher\\Middleware\\RouteOptionInspector',
            'sdispatcher.content_negotiator.class'  => 'SDispatcher\\Middleware\\ContentNegotiator',
            'sdispatcher.deserializer.class'        => 'SDispatcher\\Middleware\\Deserializer',
            'sdispatcher.serializer.class'          => 'SDispatcher\\Middleware\\Serializer',
            'sdispatcher.pagination_listener.class' => 'SDispatcher\\Middleware\\PaginationListener',
        );
    }

    public function getServices(Application $app)
    {
        return array(
            // Extends the core resolver with our CBV resolver
            'resolver'
                => $app->share($app->extend('resolver', function ($resolver, $container) {
                    return new $container['sdispatcher.cbv_resolver.class'](
                        $container,
                        $resolver);
                })),

            // delays the subscriber registration
            'dispatcher'
                => $app->share($app->extend('dispatcher', function (EventDispatcherInterface $dispatcher, $container) {
                    $dispatcher->addSubscriber($container['sdispatcher.option_inspector']);
                    $dispatcher->addSubscriber($container['sdispatcher.content_negotiator']);
                    $dispatcher->addSubscriber($container['sdispatcher.deserializer']);
                    $dispatcher->addSubscriber($container['sdispatcher.serializer']);
                    $dispatcher->addSubscriber($container['sdispatcher.pagination_listener']);
                    return $dispatcher;
                })),

            'sdispatcher.resource_option'
                => $app->share(function ($container) {
                    return new $container['sdispatcher.resource_option.class']();
                }),

            'sdispatcher.option_inspector'
                => $app->share(function ($container) {
                    return new $container['sdispatcher.option_inspector.class'](
                        $container['routes'],
                        $container['sdispatcher.resource_option']);
                }),

            'sdispatcher.content_negotiator'
                => $app->share(function ($container) {
                    return new $container['sdispatcher.content_negotiator.class'](
                        $container['routes'],
                        new FormatNegotiator());
                }),

            'sdispatcher.deserializer'
                => $app->share(function ($container) {
                    return new $container['sdispatcher.deserializer.class'](
                        $container['routes'],
                        new FOSDecoderProvider());
                }),

            'sdispatcher.serializer'
                => $app->share(function ($container) {
                    return new $container['sdispatcher.serializer.class'](
                        $container['routes'],
                        new ChainEncoder(array(
                            new JsonEncoder(),
                            new DefaultXmlEncoder())));
                }),

            'sdispatcher.pagination_listener'
                => $app->share(function ($container) {
                    return new $container['sdispatcher.pagination_listener.class']($container['routes']);
                }),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $params = $this->getDefaultParameters();
        foreach ($params as $key => $value) {
            $app[$key] = $value;
        }
        $services = $this->getServices($app);
        foreach ($services as $id => $definition) {
            $app[$id] = $definition;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
//        if ($app['sdispatcher.global_middleware']) {
//            $app->before($app['sdispatcher.option_inspector']);
//            $app->before($app['sdispatcher.content_negotiator']);
//            $app->before($app['sdispatcher.deserializer']);
//            $app->after($app['sdispatcher.serializer']);
//        }
    }
}
