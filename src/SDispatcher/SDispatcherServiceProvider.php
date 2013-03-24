<?php
namespace SDispatcher;

use SDispatcher\Middleware\ArrayToDataResponseListener;
use SDispatcher\Middleware\ContentNegotiator;
use SDispatcher\Middleware\DeserializationInspector;
use SDispatcher\Middleware\PaginationListener;
use SDispatcher\Middleware\RouteOptionInspector;
use SDispatcher\Middleware\SerializationInspector;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * It registers:
 * - `resolver`
 * - `sdispatcher.middleware.global`
 * <p/>
 * If `sdispatcher.middleware.global` is true, it will register the middlewares
 * into the global scope; otherwise, consumer must register the middleware
 * separately.
 * </p>
 * Also, there are two event subscriber will be registered:
 * - `SDispatcher\Middleware\PaginationListener`
 * - `SDispatcher\Middleware\ArrayToDataResponseListener`
 */
class SDispatcherServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['resolver'] = $app->share($app->extend(
                'resolver',
                function ($resolver, $app) {
                    return new ControllerResolver($resolver, $app);
                }));

        $globalMiddlewareId = 'sdispatcher.middleware.global';

        if (!isset($app[$globalMiddlewareId])) {
            $app[$globalMiddlewareId] = false;
        }

        if ($app[$globalMiddlewareId]) {
            $app->before(new RouteOptionInspector($app['routes']));
            $app->before(new ContentNegotiator($app['routes']));
            $app->before(new DeserializationInspector());
            $app->after(new SerializationInspector($app['routes']));
        }

        /* @var \Symfony\Component\EventDispatcher\EventDispatcher $ed */
        $ed = $app['dispatcher'];
        $ed->addSubscriber(new PaginationListener($app['routes']));
        $ed->addSubscriber(new ArrayToDataResponseListener($app['routes']));
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
