<?php

namespace SDispatcher\Middleware;

use SDispatcher\Common\ResourceOptionInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

class ControllerConfiguredMiddlewareConverter extends AbstractKernelRequestEventListener
{
    /**
     * @var \Silex\Application
     */
    protected $app;

    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    protected $routes;

    /**
     * @var \SDispatcher\Common\ResourceOptionInterface
     */
    protected $resourceOption;

    public function __construct(Application $app, RouteCollection $routes, ResourceOptionInterface $resourceOption)
    {
        $this->app = $app;
        $this->routes = $routes;
        $this->resourceOption = $resourceOption;
    }

    protected function doKernelRequest(Request $request)
    {
        $controller = $request->attributes->get('_controller');
        if (!is_string($controller) || !class_exists($controller)) {
            return null;
        }

        $routeName = $request->attributes->get('_route');
        if (!($route = $this->routes->get($routeName))) {
            return null;
        }

        try {
            $this->resourceOption->setTarget($controller);
            $beforeMiddlewares = $this->resourceOption->getBeforeMiddlewares();
            foreach ($beforeMiddlewares as $mw) {
                $route->before($this->app[$mw]);
            }
            $afterMiddlewares = $this->resourceOption->getAfterMiddlewares();
            foreach ($afterMiddlewares as $mw) {
                $route->after($this->app[$mw]);
            }
        } catch (\Exception $ex) {
        }

        return null;
    }
}
