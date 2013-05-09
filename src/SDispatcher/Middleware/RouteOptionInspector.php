<?php
namespace SDispatcher\Middleware;

use SDispatcher\Common\AnnotationResourceOption;
use SDispatcher\Common\RouteOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
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
     * @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
     */
    protected $resolver;

    /**
     * @var \SDispatcher\Common\AnnotationResourceOption
     */
    protected $annotationResourceOption;

    /**
     * @param \Symfony\Component\Routing\RouteCollection $routes
     * @param \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface $resolver
     * @param \SDispatcher\Common\AnnotationResourceOption $annotationResourceOption
     */
    public function __construct(RouteCollection $routes, ControllerResolverInterface $resolver, AnnotationResourceOption $annotationResourceOption)
    {
        $this->routes = $routes;
        $this->resolver = $resolver;
        $this->annotationResourceOption = $annotationResourceOption;
    }

    /**
     * {@inheritdoc}
     */
    protected function doKernelRequest(Request $request)
    {
        $controller = $this->resolver->getController($request);
        if (is_array($controller) && count($controller) >= 2) {
            $options = $this->resolveControllerOptions(
                $controller[0],
                $controller[1]);
            $routeName = $request->attributes->get('_route');
            $route = $this->routes->get($routeName);
            $route->addOptions($options);
        }
        $request->attributes->set('_controller', $controller);
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
        $this->annotationResourceOption->setTarget($controller, $method);
        $options = array(
            RouteOptions::SUPPORTED_FORMATS             => $this->annotationResourceOption->getSupportedFormats(),
            RouteOptions::RESOURCE_ID                   => $this->annotationResourceOption->getResourceIdentifier(),
            RouteOptions::PAGE_LIMIT                    => $this->annotationResourceOption->getPageLimit(),
            RouteOptions::PAGINATOR_CLASS               => $this->annotationResourceOption->getPaginatorClass(),
            RouteOptions::PAGINATED_DATA_CONTAINER_NAME => $this->annotationResourceOption->getPaginatedDataContainerName(),
            RouteOptions::PAGINATED_META_CONTAINER_NAME => $this->annotationResourceOption->getPaginatedMetaContainerName(),
        );
        return $options;
    }
}
