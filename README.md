Silex-Dispatcher
================

Master: [![Build Status](https://secure.travis-ci.org/bcen/silex-dispatcher.png?branch=master)](http://travis-ci.org/bcen/silex-dispatcher)
Develop: [![Build Status](https://secure.travis-ci.org/bcen/silex-dispatcher.png?branch=develop)](http://travis-ci.org/bcen/silex-dispatcher)


## Installation


Via [Composer](http://getcomposer.org/):

    {
        "require": {
            "bcen/silex-dispatcher": "dev-develop"
        }
    }

Then run ```$ composer.phar install```

## Usage

```php
$app->register(new \SDispatcher\SDispatcherServiceProvider());
```

## Features

- Django-alike CBV controller
    ```php
    class HomeController
    {
        public function get($req)
        {
            return 'Hi, '.$req->getClientIp();
        }
        
        public function post($req)
        {
            return 'This is a post';
        }
    }
    
    $app->match('/', 'HomeController');
    ```
    
    _Handle missing method_:
    ```php
    class HomeController
    {
        public function get($req)
        {
        }
        
        public function handleRequest($req)
        {
            // HEAD, OPTIONS, PUT, POST everything goes here
            // except GET, which is handle by the above method.
        }
    }
    ```
    
- Hybrid-alike Service Locator/Dependency Injection pattern

    ```php
    
    $app['my_obj'] = function () {
        $obj = new \stdClass;
        $obj->message = 'hi';
        return $obj;
    };
    
    class HomeController implements \SDispatcher\Common\RequiredServiceMetaProviderInterface
    {
        public function __construct(\stdClass $obj)
        {
            var_dump($obj);
        }
        
        public function get($req)
        {
            return 'Hi, '.$req->getClientIp();
        }

        public static function getRequiredServices()
        {
            return array('my_obj');
        }
    }
    
    $app->match('/', 'HomeController');
    
    ```

## Testing


```
$ composer.phar install --dev
$ vendor/bin/phpunit
```

## License

Silex-Dispatcher is licensed under the MIT license.
