<?php
namespace SDispatcher;

use SDispatcher\Middleware\PaginationListener;
use Silex\Application;

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
class SDispatcherServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        parent::register($app);

        if ($app[ServiceDefinitionProvider::GLOBAL_MIDDLEWARE]) {
            $app->before($app[ServiceDefinitionProvider::OPTION_INSPECTOR]);
            $app->before($app[ServiceDefinitionProvider::CONTENT_NEGOTIATOR]);
            $app->before($app[ServiceDefinitionProvider::DESERIALIZER]);
            $app->after($app[ServiceDefinitionProvider::SERIALIZER]);
        }

        /* @var \Symfony\Component\EventDispatcher\EventDispatcher $ed */
        $ed = $app['dispatcher'];
        $ed->addSubscriber($app[ServiceDefinitionProvider::PAGINATION_LISTENER]);
//        $ed->addSubscriber(new ArrayToDataResponseListener($app['routes']));
    }

    /**
     * @return \SDispatcher\ServiceDefinitionProviderInterface
     */
    public function getServiceDefinitionProvider()
    {
        return new ServiceDefinitionProvider();
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
