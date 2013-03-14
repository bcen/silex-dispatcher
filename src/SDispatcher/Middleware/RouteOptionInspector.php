<?php
namespace SDispatcher\Middleware;

use SDispatcher\Common\AnnotationResourceOption;
use SDispatcher\Common\RouteOptions;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
class RouteOptionInspector extends AbstractKernelRequestEventListener
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
     * {@inheritdoc}
     */
    protected function doKernelRequest(Request $request)
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
}
