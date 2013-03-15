<?php
namespace SDispatcher\Middleware;

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
        $routeName = $request->attributes->get('_route');
        $route = $this->routes->get($routeName);
        if (!$route) {
            return new Response('', 406);
        }

        $supportedFormats = (array)$route->getOption(
            RouteOptions::SUPPORTED_FORMATS);
        $contentType = null;

        // Look for content type in Accept header
        // e.g. Accept: text/html,application/json,*/*
        foreach ($request->getAcceptableContentTypes() as $format) {
            if (in_array($format, $supportedFormats)) {
                $contentType = $format;
                break;
            }
        }

        // Supress Accept header if query string "format" presents
        // e.g. /?format=application/json
        if (in_array($request->get('format'), $supportedFormats)) {
            $contentType = $request->get('format');
        } else {
            // allows query string format to have short hand notation
            // e.g. /?format=json
            foreach ($supportedFormats as $mimeType) {
                if (strtolower($request->getFormat($mimeType))
                    === strtolower($request->get('format'))
                ) {
                    $contentType = $mimeType;
                    break;
                }
            }
        }

        // Supress "format" query string if extension presents
        // e.g. /resource.{_format}
        foreach ($supportedFormats as $mimeType) {
            if (strtolower($request->getFormat($mimeType)
                === strtolower($request->getRequestFormat()))
            ) {
                $contentType = $mimeType;
                break;
            }
        }


        // if nothing found in query string and Accept header,
        // then use default format if */* present
        if (!$contentType
            && in_array('*/*', $request->getAcceptableContentTypes())
        ) {
            $contentType =
                is_array($supportedFormats) && isset($supportedFormats[0])
                ? $supportedFormats[0]
                : null;
        }

        if (!$contentType) {
            return new Response('', 406);
        } else {
            $route->setOption(RouteOptions::ACCEPTED_FORMAT, $contentType);
        }

        return null;
    }
}
