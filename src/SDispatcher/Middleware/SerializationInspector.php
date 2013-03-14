<?php
namespace SDispatcher\Middleware;

use SDispatcher\DataResponse;
use SDispatcher\Common\RouteOptions;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SerializationInspector implements EventSubscriberInterface
{
    /**
     * @var \Silex\Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onKernelView(FilterResponseEvent $e)
    {
        $this->doOnKernelView($e->getRequest(), $e->getResponse());
    }

    public function __invoke(Request $request, Response $response)
    {
        $this->doOnKernelView($request, $response);
    }

    protected function doOnKernelView(Request $request, Response $response)
    {
        if (!$response instanceof DataResponse) {
            return;
        }
        $routeName = $request->attributes->get('_route');
        $route = $this->app['routes']->get($routeName);
        $acceptedFormat = $route->getOption(RouteOptions::ACCEPTED_FORMAT);
        if (!$acceptedFormat) {
            $response->setContent('');
            $response->setStatusCode(406);
        }

        $serializedStr = $this->serializeDataResponse(
            $response->getContent(),
            $acceptedFormat);
        if (is_string($serializedStr)) {
            $response->setContent($serializedStr);
            $response->headers->set('Content-Type', $acceptedFormat);
        }
    }

    protected function serializeDataResponse($data, $contentType)
    {
        if ($contentType === 'application/xml') {
            // stolen from codeigniter-restserver
            // https://github.com/philsturgeon/codeigniter-restserver/blob/master/application/libraries/Format.php#L87
            $toXml = function(
                $data = null,
                $structure = null,
                $basenode = 'response'
            ) use(&$toXml) {
                if (ini_get('zend.ze1_compatibility_mode') == 1) {
                    ini_set('zend.ze1_compatibility_mode', 0);
                }
                if ($structure === null) {
                    $structure = simplexml_load_string(
                        "<?xml version='1.0' encoding='utf-8'?><$basenode />");
                }

                if (!is_array($data) && !is_object($data)) {
                    $data = (array)$data;
                }

                foreach ($data as $k => $v) {
                    if (is_bool($v)) {
                        $v = $v ? 'true' : 'false';
                    }

                    if (is_numeric($k)) {
                        $k = 'item';
                    }

                    $k = preg_replace('/[^a-z_\-0-9]/i', '', $k);

                    if (is_array($v) || is_object($v)) {
                        $node = $structure->addChild($k);
                        $toXml($v, $node, $k);
                    } else {
                        $v = htmlspecialchars(
                            html_entity_decode($v, ENT_QUOTES, 'UTF-8'),
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        $structure->addChild($k, $v);
                    }
                }

                return $structure->asXML();
            };

            return $toXml($data);
        } elseif ($contentType === 'application/json') {
            $jsonResponse = new JsonResponse($data);
            return $jsonResponse->getContent();
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::RESPONSE => 'onKernelView');
    }
}
