<?php
namespace SDispatcher\Middleware;

use SDispatcher\Common\ResourceOptionInterface;
use SDispatcher\Common\RouteOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;

/**
 * Inspects the annotations on the controller(`_controller`) and injects options
 * into the current route(`_route`).
 *
 * This class can be used as an event subscriber or as a callback middleware.<br/>
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
     * @var \SDispatcher\Common\ResourceOptionInterface
     */
    protected $resourceOption;

    /**
     * @param \Symfony\Component\Routing\RouteCollection $routes
     * @param \SDispatcher\Common\ResourceOptionInterface $resourceOption
     * @internal param \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface $resolver
     */
    public function __construct(RouteCollection $routes, ResourceOptionInterface $resourceOption)
    {
        $this->routes = $routes;
        $this->resourceOption = $resourceOption;
    }

    /**
     * {@inheritdoc}
     */
    protected function doKernelRequest(Request $request)
    {
        $routeName = $request->attributes->get('_route');
        $route = $this->routes->get($routeName);

        if (!$route || !$route->getOption(RouteOptions::REST)) {
            return null;
        }

        $controller = $request->attributes->get('_controller');

        if (is_string($controller) && class_exists($controller)) {
            $options = $this->resolveControllerOptions($controller);
            $routeName = $request->attributes->get('_route');
            $route = $this->routes->get($routeName);
            $route->addOptions($options);
        }
        return null;
    }

    /**
     * Resolves and returns the options from controller annotations.
     * @param mixed $controller
     * @return array The options
     */
    protected function resolveControllerOptions($controller)
    {
        $this->resourceOption->setTarget($controller);
        $options = array(
            RouteOptions::SUPPORTED_FORMATS             => $this->resourceOption->getSupportedFormats(),
            RouteOptions::RESOURCE_ID                   => $this->resourceOption->getResourceIdentifier(),
            RouteOptions::PAGE_LIMIT                    => $this->resourceOption->getPageLimit(),
            RouteOptions::PAGINATOR_CLASS               => $this->resourceOption->getPaginatorClass(),
            RouteOptions::PAGINATED_DATA_CONTAINER_NAME => $this->resourceOption->getPaginatedDataContainerName(),
            RouteOptions::PAGINATED_META_CONTAINER_NAME => $this->resourceOption->getPaginatedMetaContainerName(),
        );
        return $options;
    }
}
