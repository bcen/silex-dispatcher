<?php
namespace SDispatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class ControllerMethodDispatcher
{
    public function dispatch(Request $request, $controller, ControllerResolverInterface $resolver)
    {
        $methodHandler = strtolower($request->getMethod());
        if (method_exists($controller, 'handleRequest')) {
            return $controller->handleRequest($request);
        } elseif (method_exists($controller, $methodHandler)) {
            $args = $resolver->getArguments($request, array($controller, $methodHandler));
            return call_user_func_array(array($controller, $methodHandler), $args);
        } elseif (method_exists($controller, 'handleMissingMethod')) {
            return $controller->handleMissingMethod($request);
        }

        return new Response('', 405);
    }
}
