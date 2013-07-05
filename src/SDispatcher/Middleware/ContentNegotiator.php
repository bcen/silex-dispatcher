<?php
namespace SDispatcher\Middleware;

use FOS\Rest\Util\FormatNegotiatorInterface;
use SDispatcher\Common\RouteOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;

/**
 * Inspects the request to see `Accept` or `format` query string is supported or not.
 */
class ContentNegotiator extends AbstractKernelRequestEventListener
{
    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    protected $routes;

    /**
     * @var \FOS\Rest\Util\FormatNegotiatorInterface
     */
    protected $formatNegotiator;

    /**
     * @param \Symfony\Component\Routing\RouteCollection $routes
     * @param \FOS\Rest\Util\FormatNegotiatorInterface $formatNegotiator
     */
    public function __construct(RouteCollection $routes, FormatNegotiatorInterface $formatNegotiator)
    {
        $this->routes = $routes;
        $this->formatNegotiator = $formatNegotiator;
    }

    /**
     * {@inheritdoc}
     */
    protected function doKernelRequest(Request $request)
    {
        $routeName = $request->attributes->get('_route');
        $route = $this->routes->get($routeName);

        if (!$route->getOption(RouteOptions::REST)) {
            return null;
        }

        if (!$route) {
            return new Response('', 406);
        }

        if (!$request->attributes->has('_format')) {
            $request->attributes->set('_format', $request->query->get('format'));
        }


        $acceptableFormats = (array)$route->getOption(
            RouteOptions::SUPPORTED_FORMATS);
        $bestFormat = $this->formatNegotiator->getBestFormat(
            $request, $acceptableFormats, true);

        if (!$bestFormat) {
            return new Response('', 406);
        }

        $route->setOption(RouteOptions::ACCEPTED_FORMAT, $bestFormat);

        return null;
    }
}
