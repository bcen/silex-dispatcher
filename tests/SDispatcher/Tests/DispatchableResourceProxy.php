<?php
namespace SDispatcher\Tests;

use Symfony\Component\HttpFoundation\Request;
use SDispatcher\Common\ResourceOptionInterface;
use SDispatcher\Common\ResourceBundle;

class DispatchableResourceProxy extends \SDispatcher\DispatchableResource
{
    public function doDispatch(Request $request, array $routeSegments = array())
    {
        return parent::doDispatch($request, $routeSegments);
    }

    public function doResourceOptionInitialization()
    {
        parent::doResourceOptionInitialization();
    }

    public function get(Request $request, array $routeSegments = array())
    {
        return parent::get($request, $routeSegments);
    }

    public function post(Request $request, array $routeSegments = array())
    {
        return parent::post($request, $routeSegments);
    }

    public function put(Request $request, array $routeSegments = array())
    {
        return parent::put($request, $routeSegments);
    }

    public function delete(Request $request, array $routeSegments = array())
    {
        return parent::delete($request, $routeSegments);
    }

    public function doContentNegotiationCheck(Request $request)
    {
        parent::doContentNegotiationCheck($request);
    }

    public function doMethodAccessCheck(Request $request)
    {
        return parent::doMethodAccessCheck($request);
    }

    public function doAuthenticationCheck(Request $request)
    {
        parent::doAuthenticationCheck($request);
    }

    public function doAuthorizationCheck(Request $request)
    {
        parent::doAuthorizationCheck($request);
    }

    public function doSorting(ResourceBundle $bundle)
    {
        parent::doSorting($bundle);
    }

    public function doPagination(ResourceBundle $bunlde)
    {
        parent::doPagination($bunlde);
    }

    public function doSerialization(ResourceBundle $bundle, $contentType)
    {
        return parent::doSerialization($bundle, $contentType);
    }

    public function doDeserialization(ResourceBundle $bundle)
    {
        parent::doDeserialization($bundle);
    }

    public function doHydration(ResourceBundle $bundle)
    {
        parent::doHydration($bundle);
    }

    public function doDehydration(ResourceBundle $bundle)
    {
        parent::doDehydration($bundle);
    }

    public function detectSupportedContentType(Request $request)
    {
        return parent::detectSupportedContentType($request);
    }

    public function createBundle(Request $request)
    {
        return parent::createBundle($request);
    }

    public function createRawResponse()
    {
        return parent::createRawResponse();
    }

    public function finalizeResponse(ResourceBundle $bundle)
    {
        return parent::finalizeResponse($bundle);
    }

    public function triggerMethodNotAllowed(Request $request, $message = '')
    {
        parent::triggerMethodNotAllowed($request, $message);
    }

    public function triggerResourceNotFound(Request $request, $message = '')
    {
        parent::triggerResourceNotFound($request, $message);
    }

    public function getResourceOption()
    {
        return parent::getResourceOption();
    }

    public function setResourceOption(ResourceOptionInterface $option)
    {
        parent::setResourceOption($option);
    }

    public function dehydrateEmployee_id($id)
    {
        return 'confidential';
    }
}
