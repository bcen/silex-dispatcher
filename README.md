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
    
- RESTful Helpers/Middlewares

    ```php

    require __DIR__ . '/vendor/autoload.php';

    class NumberListResource
    {
        public function get()
        {
            return array(1, 2, 3, 4, 5, 6);
        }
    }
    
    class NumberDetailResource
    {
        public function get($req)
        {
            return new \SDispatcher\DataResponse(array(
                'id' => 'some_id',
                'value' => 'some value',
                'filters' => $req->query->all(),
            ));
        }
    }
    
    $app = new \Silex\Application();
    $app->register(new \SDispatcher\SDispatcherServiceProvider());
    $app->before($app['sdispatcher.option_inspector']);
    $app->before($app['sdispatcher.content_negotiator']);
    $app->before($app['sdispatcher.deserializer']);
    $app->after($app['sdispatcher.serializer']);
    
    $app->match('/numbers', 'NumberListResource');
    $app->match('/numbers/{some_id}', 'NumberDetailResource');
    
    $app->run();

    ```
    
    _Content Negotiation_:
    ```sh
    $ curl local.domain.org/api-test/numbers/1 -H "Accept:application/xml" -i
    HTTP/1.1 406 Not Acceptable
    Date: Sat, 18 May 2013 00:28:58 GMT
    Server: Apache/2.4.3 (Win32) OpenSSL/1.0.1c PHP/5.4.7
    X-Powered-By: PHP/5.4.7
    Cache-Control: no-cache
    Content-Length: 0
    Content-Type: text/html; charset=UTF-8
    ```
    
    _Automated Serialization_:
    ```sh
    $ curl local.domain.org/api-test/numbers/1 -H "Accept:application/json" -i
    HTTP/1.1 200 OK
    Date: Sat, 18 May 2013 00:29:30 GMT
    Server: Apache/2.4.3 (Win32) OpenSSL/1.0.1c PHP/5.4.7
    X-Powered-By: PHP/5.4.7
    Cache-Control: no-cache
    Content-Length: 50
    Content-Type: application/json
    
    {"id":"some_id","value":"some value","filters":[]}
    ```
    
    _Automated Pagination_:
    ```sh
    $ curl local.domain.org/api-test/numbers
    {"meta":{"offset":0,"limit":20,"total":6,"prevLink":null,"nextLink":null},"objects":[1,2,3,4,5,6]}
    ```
    
    _Automated 405 Response for missing method handler_:
    ```sh
    $ curl local.domain.org/api-test/numbers -i -X POST
    HTTP/1.1 405 Method Not Allowed
    Date: Sat, 18 May 2013 01:21:20 GMT
    Server: Apache/2.4.3 (Win32) OpenSSL/1.0.1c PHP/5.4.7
    X-Powered-By: PHP/5.4.7
    Cache-Control: no-cache
    Content-Length: 0
    Content-Type: text/html; charset=UTF-8
    ```
    
    __NOTE__: Remember to turn on URL rewrite!!

## Testing


```
$ composer.phar install --dev
$ vendor/bin/phpspec
$ vendor/bin/behat
```

## License

Silex-Dispatcher is licensed under the MIT license.
