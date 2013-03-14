<?php
namespace SDispatcher\Middleware;

use SDispatcher\Common\AnnotationResourceOption;
use SDispatcher\Common\RouteOptions;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Inspects the annotations on the controller(`_controller`) and injects options
 * into the current route(`_route`).
 *
 * This class cann be used as an event subscriber or as callback middleware.<br/>
 * e.g.
 * <code>
 * $dispatcher->addSubscriber(new RouteOptionInspector($routes));
 * // or in Silex
 * $app->before(new RouteOptionInspector($app['routes']);
 * // or even like this
 * $connector = $app['controllers_factory'];
 * $connector->before(new RouteOptionInspector($app['routes']);
 * </code>
 *
 * @see \SDispatcher\Common\RouteOptions
 */
class RouteOptionInspector implements EventSubscriberInterface
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
     * Inspects the class-based controller for routing option. (i.e @PageLimit)
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $e
     */
    public function onKernelRequest(GetResponseEvent $e)
    {
        $ret = $this->doOnKernelRequest($e->getRequest());
        if ($ret instanceof Response) {
            $e->setResponse($ret);
        }
    }

    /**
     * Same as {@see onKernelRequest}. But it will be used as a callback.
     * <code>
     * $app->before(new RouteOptionInspector($routes));
     * // or standalone
     * $inspector = new RouteOptionInspector($routes);
     * $inspector($request);
     * call_user_func($inspector, $request); // valid also
     * </code>
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request)
    {
        return $this->doOnKernelRequest($request);
    }

    /**
     * Does the actual work for {@link __invoke()}
     * and {@link onKernelRequest()}.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return null
     */
    protected function doOnKernelRequest(Request $request)
    {
        $controllerStr = $request->attributes->get('_controller');
        if (!is_string($controllerStr)) {
            return;
        }
        $controller = explode('::', $controllerStr);
        if (is_array($controller) && count($controller) >= 2) {
            $options = $this->resolveControllerOptions(
                $controller[0],
                $controller[1]);
            $routeName = $request->attributes->get('_route');
            $route = $this->routes->get($routeName);
            $route->addOptions($options);
        }
        return null;
    }

    /**
     * Resolves and returns the options from controller annotations.
     * @param mixed $controller
     * @param string $method
     * @return array The options
     */
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
