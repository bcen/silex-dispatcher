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
        $method = null;

        if (method_exists($controller, 'handleRequest')) {
            $method = 'handleRequest';
        } elseif (method_exists($controller, $methodHandler)) {
            $method = $methodHandler;
        } elseif (method_exists($controller, 'handleMissingMethod')) {
            $method = 'handleMissingMethod';
        }

        if ($method) {
            $args = $resolver->getArguments($request, array($controller, $method));
            return call_user_func_array(array($controller, $method), $args);
        }

        return new Response('', 405);
    }
}
