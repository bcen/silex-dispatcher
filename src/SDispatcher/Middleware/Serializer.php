<?php
namespace SDispatcher\Middleware;

use SDispatcher\Common\RouteOptions;
use SDispatcher\DataResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollection;

class Serializer implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    protected $routes;

    /**
     * @param \Symfony\Component\Routing\RouteCollection $routes
     */
    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @see doKernelView()
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $e
     */
    public function onKernelView(FilterResponseEvent $e)
    {
        $this->doKernelView($e->getRequest(), $e->getResponse());
    }

    /**
     * @see doKernelView()
     * @param Request $request
     * @param Response $response
     */
    public function __invoke(Request $request, Response $response)
    {
        $this->doKernelView($request, $response);
    }

    protected function doKernelView(Request $request, Response $response)
    {
        if (!$response instanceof DataResponse) {
            return;
        }
        $routeName = $request->attributes->get('_route');
        $route = $this->routes->get($routeName);
        $acceptedFormat = $route->getOption(RouteOptions::ACCEPTED_FORMAT);
        if (!$acceptedFormat) {
            $response->setContent('');
            $response->setStatusCode(406);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::RESPONSE => 'onKernelView');
    }
}
