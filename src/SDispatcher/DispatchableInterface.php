<?php
namespace SDispatcher;

use Symfony\Component\HttpFoundation\Request;

interface DispatchableInterface
{
    /**
     * Dispatches the request and returns a resposne.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $routeSegments
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function doDispatch(Request $request, array $routeSegments = array());
}
