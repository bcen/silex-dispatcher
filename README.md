Silex-Dispatcher
================

Master: [![Build Status](https://secure.travis-ci.org/bcen/silex-dispatcher.png?branch=master)](http://travis-ci.org/bcen/silex-dispatcher)
Develop: [![Build Status](https://secure.travis-ci.org/bcen/silex-dispatcher.png?branch=develop)](http://travis-ci.org/bcen/silex-dispatcher)


## Installation


Via [Composer](http://getcomposer.org/):

    {
        "require": {
            "bcen/silex-dispatcher": "0.2.*"
        }
    }

Then run ```$ composer.phar install```

## Usage

```php

$app->register(new \SDispatcher\SDispatcherServiceProvider());

// or registers the middlewares into global scope

$app->register(new \SDispatcher\SDispatcherServiceProvider(), array(
    'sdispatcher.middleware.global' => true
));

```

## Features

- `SDispatcher\AbstractServiceProvider`

    It provides a better and cleaner way to register service defintion.
    
    ```php
    <?php
    
    class MyServiceProvider extends \SDispatcher\AbstractServiceProvider
    {
        public function getServiceDefinitionProvider()
        {
            return new MyServiceDefinitionProvider();
        }
    }
    
    class MyServiceDefinitionProvider implements \SDispatcher\ServiceDefinitionProviderInterface
    {
        public function getServices(\Silex\Application $app)
        {
            return array(
                'my.service1' => $app->share(function ($container) {
                    // ...
                }),
            );
        }
    }
    
    ```
    
- `SDispatcher\Middleware\RouteOptionInspector`

    As Silex middleware:
    ```php
    $app->before(new \SDispatcher\Middleware\RouteOptionInspector($app['routes']));
    ```
    
    As event subscriber:
    ```php
    $app['dispatcher']->addSubscriber(new \SDispatcher\Middleware\RouteOptionInspector($app['routes']));
    ```
    
    `RouteOptionInspector` inspects the annotations on class-based controller and resolves them into the
    current route option.

    See `\SDispatcher\Common\RouteOptions` for available options.  
    See `\SDispatcher\Common\Annotation\*` for available annotations.
    
    Usage:
    
    ```php
    
    use SDispatcher\Common\Annotation\SupportedFormats;
    
    /**
     * @SupportedFormats({"application/xml", "application/json"})
     */
    class ClassBasedController
    {
        public function index()
        {
        }
    }
    
    ```
    
    The above will resolve into:

    ```php
    $routeName = $request->attributes->get('_route');
    $app['routes']->get($routeName)->setOption(
        'sdispatcher.route.supported_formats', 
        array('application/xml', 'application/json'));
    ```

## Testing


```
$ composer.phar install --dev
$ vendor/bin/phpunit
```

## License

Silex-Dispatcher is licensed under the MIT license.
