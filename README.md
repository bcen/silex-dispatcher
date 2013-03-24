Silex-Dispatcher
================

Master: [![Build Status](https://secure.travis-ci.org/bcen/silex-dispatcher.png?branch=master)](http://travis-ci.org/bcen/silex-dispatcher)
Develop: [![Build Status](https://secure.travis-ci.org/bcen/silex-dispatcher.png?branch=develop)](http://travis-ci.org/bcen/silex-dispatcher)

Silex-Dispatcher pretends to be a RESTful framework that built on top of
[Silex](http://silex.sensiolabs.org/).
It is inspired by [django-tastypie](https://github.com/toastdriven/django-tastypie)
(No, not really, no inspiration, I copied django-tastypie line by line).


## Installation


Via [Composer](http://getcomposer.org/):

    {
        "require": {
            "bcen/silex-dispatcher": "0.1.*"
        }
    }

Then run ```$ composer.phar install```

## Usage


`index.php`

```php
<?php

use SDispatcher\Common\ResourceBundle;

require __DIR__ . '/vendor/autoload.php';

$app = new \Silex\Application();
$app->register(new \SDispatcher\DispatchingServiceProvider());

class NumbersResource extends \SDispatcher\DispatchableResource
{
    public function readList(ResourceBundle $bundle)
    {
        return array(1, 2, 3, 4);
    }
}

$app['sdispatcher.controller_factory']->makeRoute($app, '/numbers', 'NumbersResource');

$app['debug'] = true;
$app->run();

```

```
$ curl http://domain.com/numbers
{"meta":{"offset":0,"limit":20,"total":4,"prevLink":null,"nextLink":null},"objects":[1,2,3,4]}

$ curl http://domain.com/numbers?limit=1
{
    "meta": {
        "offset": 0,
        "limit": 1,
        "total": 4,
        "prevLink": null,
        "nextLink": "http://domain.com/numbers?limit=1&offset=1"
    },
    "objects": [
        1
    ]
}
````

Note: You need URL rewrite to have a pretty URL; otherwise, http://domain.com/index.php/numbers.

## Internal

- ###### `\SDispatcher\ControllerFactory`
    It is reponsible for generating an anonymous function
    for a Silex route from a fqcn (Fully-Qualified Class Name) string and an optional route segments array.
    e.g.
    
    ```php
    $app->get('/numbers', $controllerFactory->createClosure('NumbersResource'));
    // or alternative syntax
    $app->get('/numbers', $controllerFactory('NumbersResource'));

    // with route segments
    $app->get('/show/{id}', $controllerFactory('ShowController', array('id')));
    
    // shortcut to map a class to 'GET', 'POST', 'PUT', 'DELETE' by default.
    // $delegate can be an instance of \Silex\Application or \Silex\ControllerCollection.
    $controllerFactory->makeRoute($delegate, '/about', 'AboutController');
    
    // equivalent of above
    $delegate->match('/about', $controllerFactory('AboutController'))->method('GET|POST|PUT|DELETE');
    ```
    Note: The mapped class must implement `\SDispatcher\DispatchableInterface`.
    
    The generated anonymous function does four things.
    - Gets all the values of the route segments. (e.g. '/show/{id}' -> '/show/1' -> '1')
    - Creates a new instance of the mapped class and attemp to resolve the constructor dependencies with `\SDispatcher\Common\ClassResolver`
    - Then call "doDispatch" on the mapped instance
    - Returns the response object from "doDispatch"

## Testing


```
$ composer.phar install --dev
$ vendor/bin/phpunit
```

## License

Silex-Dispatcher is licensed under the MIT license.
