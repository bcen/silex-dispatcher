<?php
namespace SDispatcher\Middleware;

use SDispatcher\Common\RouteOptions;
use SDispatcher\DataResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollection;

class PaginationListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    protected $routes;

    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $e
     */
    public function onKernelView(GetResponseForControllerResultEvent $e)
    {
        $queryset = $e->getControllerResult();
        if (!is_array($queryset)) {
            return;
        }

        $routeName = $e->getRequest()->attributes->get('_route');
        $route = $this->routes->get($routeName);
        if (!$route || !$route->getOption(RouteOptions::WILL_PAGINGATE)) {
            return;
        }

        $interface = 'SDispatcher\\Common\\PaginatorInterface';
        $paginatorClass = $route->getOption(RouteOptions::PAGINATOR_CLASS);
        if (!$paginatorClass || !is_subclass_of($paginatorClass, $interface)) {
            return;
        }

        /* @var \SDispatcher\Common\PaginatorInterface $paginator */
        $paginator = new $paginatorClass();
        list($headers, $data) = $paginator->paginate(
            $e->getRequest(),
            $queryset,
            0,
            $route->getOption(RouteOptions::PAGE_LIMIT),
            $route->getOption(RouteOptions::PAGINATED_META_CONTAINER_NAME),
            $route->getOption(RouteOptions::PAGINATED_DATA_CONTAINER_NAME));

        $e->setControllerResult($data);
        $response = new DataResponse($data);
        $response->headers->add($headers);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::VIEW => 'onKernelView');
    }
}
