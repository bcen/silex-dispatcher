<?php
namespace SDispatcher;

use FOS\Rest\Util\FormatNegotiator;
use SDispatcher\Common\AnnotationResourceOption;
use SDispatcher\Common\DefaultXmlEncoder;
use SDispatcher\Common\FOSDecoderProvider;
use SDispatcher\Middleware\ContentNegotiator;
use SDispatcher\Middleware\Deserializer;
use SDispatcher\Middleware\PaginationListener;
use SDispatcher\Middleware\RouteOptionInspector;
use SDispatcher\Middleware\Serializer;
use Silex\Application;
use Silex\ServiceProviderInterface;
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
            'sdispatcher.global_middleware' => false,
        );
    }

    public function getServices(Application $app)
    {
        return array(
            'resolver'
                => $app->share($app->extend('resolver', function ($resolver) {
                    return new CbvControllerResolver($resolver);
                })),

            'sdispatcher.resource_option'
                => $app->share(function () {
                    return new AnnotationResourceOption();
                }),

            'sdispatcher.option_inspector'
                => $app->share(function ($container) {
                    return new RouteOptionInspector(
                        $container['routes'],
                        $container['sdispatcher.resource_option']);
                }),

            'sdispatcher.content_negotiator'
                => $app->share(function ($container) {
                    return new ContentNegotiator(
                        $container['routes'],
                        new FormatNegotiator());
                }),

            'sdispatcher.deserializer'
                => $app->share(function () {
                    return new Deserializer(new FOSDecoderProvider());
                }),

            'sdispatcher.serializer'
                => $app->share(function ($container) {
                    return new Serializer(
                        $container['routes'],
                        new ChainEncoder(array(
                            new JsonEncoder(),
                            new DefaultXmlEncoder())
                        ));
                }),

            'sdispatcher.pagination_listener'
                => $app->share(function ($container) {
                    return new PaginationListener($container['routes']);
                }),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $params = $this->getDefaultParameters();
        foreach ((array)$params as $key => $value) {
            if (!isset($app[$key])) {
                $app[$key] = $value;
            }
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
        if ($app['sdispatcher.global_middleware']) {
            $app->before($app['sdispatcher.resource_option']);
            $app->before($app['sdispatcher.content_negotiator']);
            $app->before($app['sdispatcher.deserializer']);
            $app->after($app['sdispatcher.serializer']);
        }

        /* @var \Symfony\Component\EventDispatcher\EventDispatcher $ed */
        $ed = $app['dispatcher'];
        $ed->addSubscriber($app['sdispatcher.pagination_listener']);
    }
}
