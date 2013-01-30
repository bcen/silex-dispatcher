<?php
namespace SDispatcher;

use SDispatcher\Common\ResourceOptionInterface;
use SDispatcher\Common\DefaultResourceOption;
use SDispatcher\Common\ResourceBundle;
use SDispatcher\Exception\ResourceNotFoundException;
use SDispatcher\Exception\DispatchingHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class DispatchableResource implements DispatchableInterface
{
    /**
     * @var \SDispatcher\Common\ResourceOptionInterface
     */
    private $resourceOption;

    /**
     * {@inheritdoc}
     */
    public function doDispatch(Request $request, array $routeSegments = array())
    {
        $routeSegments = array_values($routeSegments);
        $response = new Response('Sumting Wrong', 404);

        try {
            $this->doResourceOptionInitialization();
            $this->doContentNegotiationCheck($request);
            $this->doMethodAccessCheck($request, $routeSegments);
            $this->doAuthenticationCheck($request);
            $this->doAuthorizationCheck($request);

            $method = strtolower($request->getMethod());
            $response = $this->$method($request, $routeSegments);
        } catch (DispatchingHttpException $ex) {
            $response = $ex->getResponse();
        }

        return $response;
    }

    /**
     * Hook point for initializing resource option. This will be called
     * at the begining of {@see doDispatch()}.
     */
    protected function doResourceOptionInitialization()
    {
    }

    /**
     * Default GET request handler.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $routeSegments
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function get(Request $request, array $routeSegments = array())
    {
        $bundle = $this->createBundle($request);
        $args = $routeSegments;
        array_unshift($args, $bundle);

        switch (true) {

            // GET /{resource}/schema/  ->  invoke "readSchema"
            case count($routeSegments) === 1 && $routeSegments[0] === 'schema':

                $schema = call_user_func_array(
                    array($this, 'readSchema'),
                    $args
                );
                $bundle->setData($schema);
                break;

            // GET /{resource}/{id}/{segments}?  ->  invoke "readDetail"
            case !empty($routeSegments):

                $detail = call_user_func_array(
                    array($this, 'readDetail'),
                    $args
                );
                if ($detail === null) {
                    $this->triggerResourceNotFound($request);
                }
                $bundle->setData($detail);
                break;

            // GET /{resource/  ->  invoke "readList"
            default:

                $list = call_user_func_array(
                    array($this, 'readList'),
                    $args
                );
                $bundle->setData($list);
                $this->doSorting($bundle);
                $this->doPagination($bundle);

        }

        return $this->finalizeResponse($bundle);
    }

    /**
     * Default POST request handler.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $routeSegments
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function post(Request $request, array $routeSegments = array())
    {
        $bundle = $this->createBundle($request);
        $args = $routeSegments;
        array_unshift($args, $bundle);

        $data = call_user_func_array(
            array($this, 'createResource'),
            $args
        );

        $bundle->setData($data);
        $id = $bundle->getData('id');

        if ($id) {
            $location = $request->getUriForPath($request->getPathInfo());
            $location = rtrim($location, '/') . '/' . $id;
            $bundle->getResponse()->headers->set('Location', $location);
        }

        $bundle->getResponse()->setStatusCode(201);
        return $this->finalizeResponse($bundle);
    }

    /**
     * Default PUT request handler.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $routeSegments
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function put(Request $request, array $routeSegments = array())
    {
        $bundle = $this->createBundle($request);
        $args = $routeSegments;
        array_unshift($args, $bundle);

        switch (true) {
            // PUT /{resource}/  ->  invoke "updateList"
            case empty($routeSegments):
                $data = call_user_func_array(
                    array($this, 'updateList'),
                    $args
                );
                $bundle->setData($data);
                break;

            // PUT /{resource}/{id}/{routeSegments}?  ->  invoke "updateDetail"
            default:
                $data = call_user_func_array(
                    array($this, 'updateDetail'),
                    $args
                );
                $bundle->setData($data);
        }

        $bundle->getResponse()->setStatusCode(202);
        return $this->finalizeResponse($bundle);
    }

    /**
     * Default DELETE request handler.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $routeSegments
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function delete(Request $request, array $routeSegments = array())
    {
        $bundle = $this->createBundle($request);
        $args = $routeSegments;
        array_unshift($args, $bundle);

        switch (true) {
            case empty($routeSegments):
                $data = call_user_func_array(
                    array($this, 'deleteList'),
                    $args
                );
                $bundle->setData($data);
                break;

            default:
                $data = call_user_func_array(
                    array($this, 'deleteDetail'),
                    $args
                );
                $bundle->setData($data);
        }

        return $this->finalizeResponse($bundle);
    }

    /**
     * Checks for acceptable Content-Type.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \SDispatcher\Exception\DispatchingHttpException If no acceptable Content-Type found
     */
    protected function doContentNegotiationCheck(Request $request)
    {
        if ($this->detectSupportedContentType($request) === null) {
            throw new DispatchingHttpException(new Response('', 406));
        }
    }

    /**
     * Checks whether the request method is allowed.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \SDispatcher\Exception\DispatchingHttpException
     */
    protected function doMethodAccessCheck(Request $request)
    {
        $allowedMethods = $this->getResourceOption()->getAllowedMethods();
        $method = $request->getMethod();

        $allowed = array_filter($allowedMethods, function ($e) use ($method) {
            return strtolower($e) === strtolower($method);
        });

        if (empty($allowed)) {
            $this->triggerMethodNotAllowed($request);
        }
    }

    /**
     * Hook point for authentication check.
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function doAuthenticationCheck(Request $request)
    {
    }

    /**
     * Hook point for authorization check
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function doAuthorizationCheck(Request $request)
    {
    }

    /**
     * Sorts the bundle data.
     * @param \SDispatcher\Common\ResourceBundle $bundle
     */
    protected function doSorting(ResourceBundle $bundle)
    {
    }

    /**
     * Paginates the data in the bundle.
     * @param \SDispatcher\Common\ResourceBundle $bunlde
     */
    protected function doPagination(ResourceBundle $bunlde)
    {
        $request = $bunlde->getRequest();
        $paginator = $this->getResourceOption()->getPaginator();
        list($headers, $data) = $paginator->paginate(
            $request,
            $bunlde->getData(),
            0,
            $this->getResourceOption()->getPageLimit(),
            $this->getResourceOption()->getPaginatedMetaContainerName(),
            $this->getResourceOption()->getPaginatedDataContainerName()
        );
        $bunlde->setData($data);
        $bunlde->getResponse()->headers->add($headers);
    }

    /**
     * Serializes the data in the bundle.
     * @param \SDispatcher\Common\ResourceBundle $bundle
     * @param string $contentType The format type
     */
    protected function doSerialization(ResourceBundle $bundle, $contentType)
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

            $response = $bundle->getResponse();
            $data = $toXml($bundle->getData());
            $bundle->setData($data);
            $response->setContent($bundle->getData());
            $response->headers->set('Content-Type', $contentType);
        } elseif ($contentType === 'application/json') {
            $response = $bundle->getResponse();
            $jsonResponse = new JsonResponse(
                $bundle->getData(),
                $response->getStatusCode(),
                $response->headers->all()
            );
            $bundle->setResponse($jsonResponse);
            $bundle->setData($bundle->getResponse()->getContent());
        }
    }

    /**
     * Deserializes the data in the bundle.
     * @param \SDispatcher\Common\ResourceBundle $bundle
     */
    protected function doDeserialization(ResourceBundle $bundle)
    {
    }

    /**
     * Hydrates the data in the bundle.
     * @param \SDispatcher\Common\ResourceBundle $bundle
     */
    protected function doHydration(ResourceBundle $bundle)
    {
    }

    /**
     * Dehydrates the data in the bundle.
     * @param \SDispatcher\Common\ResourceBundle $bundle
     */
    protected function doDehydration(ResourceBundle $bundle)
    {
        $request = $bundle->getRequest();
        $data = $bundle->getData();

        $addSelfLink = function (array &$objects, $link, $attr) {
            $link = rtrim($link, '/') . '/';
            foreach ($objects as $key => $val) {
                if (is_array($val) && isset($val[$attr])) {
                    $val['selfLink'] = $link . $val[$attr];
                    $objects[$key] = $val;
                }
            }
        };

        $self = $this;
        $dehydrateField = function (array &$objects) use ($self) {
            foreach ($objects as $index => $obj) {
                foreach ($obj as $key => $val) {
                    $method = 'dehydrate' . ucfirst($key);
                    if (method_exists($self, $method)) {
                        $val = call_user_func(array($self, $method), $val);
                        $obj[$key] = $val;
                        $objects[$index] = $obj;
                    }
                }
            }
        };

        $link = $request->getSchemeAndHttpHost() .
                $request->getBaseUrl() .
                $request->getPathInfo();

        $resourceIdentifier = $this
            ->getResourceOption()
            ->getResourceIdentifier();

        $containerName = $this
            ->getResourceOption()
            ->getPaginatedDataContainerName();
        if (is_array($data) && isset($data[$containerName])) {
            $objects = $data[$containerName];
            $addSelfLink($objects, $link, $resourceIdentifier);
            $dehydrateField($objects);
            $data[$containerName] = $objects;
        } elseif (is_array($data)) {
            $objects = array($data);
            $dehydrateField($objects);
            $data = array_shift($objects);
        }

        $bundle->setData($data);
    }

    /**
     * Determines the supported Content-Type from request.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string|null The supported Content-Type if found; otherwise, null
     */
    protected function detectSupportedContentType(Request $request)
    {
        $supportedFormats = $this->getResourceOption()->getSupportedFormats();
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
            $contentType = $this->getResourceOption()->getDefaultFormat();
        }

        return $contentType;
    }

    /**
     * Creates a resource bundle, and deserializaes/hydrates the data.
     * A resource bundle includes a
     * {@link \Symfony\Component\HttpFoundation\Request},
     * {@link \Symfony\Component\HttpFoundation\Response} and mixed $data.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \SDispatcher\Common\ResourceBundle
     */
    protected function createBundle(Request $request)
    {
        $bundle = new ResourceBundle($request);
        $bundle->setResponse($this->createRawResponse());
        $this->doDeserialization($bundle);
        $this->doHydration($bundle);
        return $bundle;
    }

    /**
     * Creates a raw response.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function createRawResponse()
    {
        return new Response();
    }

    /**
     * Prepares the response for output.
     * - Dehydrates the bundle data
     * - Serializes the bundle data
     * @param \SDispatcher\Common\ResourceBundle $bundle
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function finalizeResponse(ResourceBundle $bundle)
    {
        $this->doDehydration($bundle);
        $contentType = $this->detectSupportedContentType($bundle->getRequest());
        $this->doSerialization($bundle, $contentType);
        $response = $bundle->getResponse();

        return $response;
    }

    /**
     * Helper method that triggers a {@link \SDispatcher\Exception\DispatchingHttpException} for 405: Method Not Allowed.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $message Optional error message
     * @throws \SDispatcher\Exception\DispatchingHttpException
     */
    protected function triggerMethodNotAllowed(Request $request, $message = '')
    {
        $bundle = $this->createBundle($request);
        $bundle->setData(array(
            'errorMessage' => ($message !== '') ? $message : 'Not Allowed'
        ));
        $bundle->getResponse()->setStatusCode(405);
        throw new DispatchingHttpException($this->finalizeResponse($bundle));
    }

    /**
     * Helper method that triggers a {@link \SDispatcher\Exception\ResourceNotFoundException}
     * and properly serializes the response by calling {@see createBundle()}
     * and {@see finalizeResponse()}.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $message
     * @throws \SDispatcher\Exception\ResourceNotFoundException
     */
    protected function triggerResourceNotFound(Request $request, $message = '')
    {
        $bunlde = $this->createBundle($request);
        $bunlde->setData(array(
            'errorMessage' => ($message !== '') ? $message : 'Not Found'
        ));
        $bunlde->getResponse()->setStatusCode(404);
        throw new ResourceNotFoundException($this->finalizeResponse($bunlde));
    }

    /**
     * @return \SDispatcher\Common\ResourceOptionInterface
     */
    public function getResourceOption()
    {
        if (!$this->resourceOption) {
            $this->resourceOption = new DefaultResourceOption();
        }
        return $this->resourceOption;
    }

    /**
     * @param \SDispatcher\Common\ResourceOptionInterface $option
     */
    public function setResourceOption(ResourceOptionInterface $option)
    {
        $this->resourceOption = $option;
    }
}
