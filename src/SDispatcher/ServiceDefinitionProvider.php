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
use Symfony\Component\Serializer\Encoder\ChainEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class ServiceDefinitionProvider implements ServiceDefinitionProviderInterface
{
    // parameters
    const GLOBAL_MIDDLEWARE   = 'sdispatcher.middleware.global';

    // services
    const RESOURCE_OPTION     = 'sdispatcher.common.annotation_resource_option';
    const OPTION_INSPECTOR    = 'sdispatcher.middleware.route_option_inspector';
    const CONTENT_NEGOTIATOR  = 'sdispatcher.middleware.content_negotiator';
    const DESERIALIZER        = 'sdispatcher.middleware.deserializer';
    const SERIALIZER          = 'sdispatcher.middleware.serializer';
    const PAGINATION_LISTENER = 'sdispatcher.middleware.pagination_listener';

    public function getDefaultParameters(Application $app)
    {
        return array(
            static::GLOBAL_MIDDLEWARE => false,
        );
    }

    public function getServices(Application $app)
    {
        return array(
            static::RESOURCE_OPTION     => $this->defineAnnotationResourceOption($app),
            static::OPTION_INSPECTOR    => $this->defineRouteOptionInspector($app),
            static::CONTENT_NEGOTIATOR  => $this->defineNegotiator($app),
            static::DESERIALIZER        => $this->defineDeserializer($app),
            static::SERIALIZER          => $this->defineSerializer($app),
            static::PAGINATION_LISTENER => $this->definePaginationListener($app),
        );
    }

    public function defineAnnotationResourceOption(Application $app)
    {
        return $app->share(function () {
            return new AnnotationResourceOption();
        });
    }

    public function defineRouteOptionInspector(Application $app)
    {
        $rcOption = static::RESOURCE_OPTION;
        return $app->share(function ($container) use ($rcOption) {
            return new RouteOptionInspector(
                $container['routes'],
                $container['resolver'],
                $container[$rcOption]);
        });
    }

    public function defineNegotiator(Application $app)
    {
        return $app->share(function ($container) {
            return new ContentNegotiator(
                $container['routes'],
                new FormatNegotiator());
        });
    }

    public function defineDeserializer(Application $app)
    {
        return $app->share(function () {
            return new Deserializer(new FOSDecoderProvider());
        });
    }

    public function defineSerializer(Application $app)
    {
        return $app->share(function ($container) {
            return new Serializer(
                $container['routes'],
                new ChainEncoder(array(new JsonEncoder(), new DefaultXmlEncoder())));
        });
    }

    public function definePaginationListener(Application $app)
    {
        return $app->share(function ($container) {
            return new PaginationListener($container['routes']);
        });
    }
}
