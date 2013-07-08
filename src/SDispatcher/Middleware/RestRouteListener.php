<?php

namespace SDispatcher\Middleware;

use SDispatcher\Common\RouteOptions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollection;

final class RestRouteListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    private $routes;

    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    public function onKernelRequest(GetResponseEvent $e)
    {
        if (!$this->isRestRoute($e->getRequest())) {
            return;
        }

        $e->getDispatcher()->dispatch('sdispatcher.rest_request', $e);
    }

    public function onKernelResponse(FilterResponseEvent $e)
    {
        if (!$this->isRestRoute($e->getRequest())) {
            return;
        }

        $e->getDispatcher()->dispatch('sdispatcher.rest_response', $e);
    }

    public function onKernelView(GetResponseForControllerResultEvent $e)
    {
        if (!$this->isRestRoute($e->getRequest())) {
            return;
        }

        $e->getDispatcher()->dispatch('sdispatcher.rest_view', $e);
    }

    private function isRestRoute(Request $request)
    {
        $routeName = $request->attributes->get('_route');
        $route = $this->routes->get($routeName);

        if (!$route || !$route->getOption(RouteOptions::REST)) {
            return false;
        }

        return true;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST  => array('onKernelRequest', 4),
            KernelEvents::RESPONSE => array('onKernelResponse', -4),
            KernelEvents::VIEW     => 'onKernelView',
        );
    }
}
