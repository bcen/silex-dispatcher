<?php
namespace SDispatcher\Middleware;

use FOS\Rest\Decoder\DecoderProviderInterface;
use SDispatcher\Common\RouteOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

class Deserializer extends AbstractKernelRequestEventListener
{
    /**
     * @var \FOS\Rest\Decoder\DecoderProviderInterface
     */
    private $decoderProvider;

    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    private $routes;

    /**
     * @param \Symfony\Component\Routing\RouteCollection $routes
     * @param \FOS\Rest\Decoder\DecoderProviderInterface $decoderProvider
     */
    public function __construct(RouteCollection $routes, DecoderProviderInterface $decoderProvider)
    {
        $this->routes = $routes;
        $this->decoderProvider = $decoderProvider;
    }

    protected function doKernelRequest(Request $request)
    {
        $routeName = $request->attributes->get('_route');
        $route = $this->routes->get($routeName);

        if (!$route->getOption(RouteOptions::REST)) {
            return;
        }

        $format = $request->getFormat(
            $request->headers->get('Content-Type', null, true));
        if ($this->decoderProvider->supports($format)) {
            /** @var \FOS\Rest\Decoder\DecoderInterface $decoder */
            $decoder = $this->decoderProvider->getDecoder($format);
            $data = $decoder->decode($request->getContent());
            $request->request->replace(is_array($data) ? $data : array());
        }
    }
}
