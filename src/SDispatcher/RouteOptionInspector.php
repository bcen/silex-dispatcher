<?php
namespace SDispatcher;

use SDispatcher\Common\AnnotationResourceOption;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RouteOptionInspector implements EventSubscriberInterface
{
    /**
     * @var \Silex\Application
     */
    protected $app;

    /**
     * @param \Silex\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Inspects the class based controller for routing option. (i.e @PageLimit)
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $e
     */
    public function onKernelRequest(GetResponseEvent $e)
    {
        $request = $e->getRequest();
        $this->doOnKernelRequest($request);
    }

    /**
     * Same as {@see onKernelRequest}. But it will be used as a callback.
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function __invoke(Request $request)
    {
        $this->doOnKernelRequest($request);
    }

    protected function doOnKernelRequest(Request $request)
    {
        $controllerStr = $request->attributes->get('_controller');
        if (!is_string($controllerStr)) {
            return;
        }
        $controller = explode('::', $controllerStr);
        if (is_array($controller) && count($controller >= 2)) {
            $options = $this->resolveControllerOptions(
                $controller[0],
                $controller[1]);
            $routeName = $request->attributes->get('_route');
            $route = $this->app['routes']->get($routeName);
            foreach ((array)$options as $k => $v) {
                $route->setOption($k, $v);
            }
        }
    }

    protected function resolveControllerOptions($controller, $method)
    {
        $resourceOption = new AnnotationResourceOption($controller, $method);
        $options = array(
            RouteOptions::SUPPORTED_FORMATS => $resourceOption->getSupportedFormats(),
            RouteOptions::RESOURCE_ID       => $resourceOption->getResourceIdentifier(),
            RouteOptions::PAGE_LIMIT        => $resourceOption->getPageLimit(),
            RouteOptions::WILL_PAGINGATE    => $resourceOption->willPaginate()
        );
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => 'onKernelRequest');
    }
}
