<?php
namespace SDispatcher;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentNegotiator implements EventSubscriberInterface
{
    /**
     * @var \Silex\Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onKernelRequest(GetResponseEvent $e)
    {
        return $this->doKernelRequest($e->getRequest());
    }

    public function __invoke(Request $request)
    {
        return $this->doKernelRequest($request);
    }

    protected function doKernelRequest(Request $request)
    {
        $routeName = $request->attributes->get('_route');
        $route = $this->app['routes']->get($routeName);
        if (!$route) {
            return new Response('', 406);
        }

        $supportedFormats = (array)$route->getOption(
            'sdispatcher.route.supported_formats');
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
            $route->setOption(
                'sdispatcher.route.accepted_format',
                $contentType);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => 'onKernelRequest');
    }
}
