<?php
namespace SDispatcher;

use Silex\Application;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Resolves the given controller class string into closure.
 */
abstract class AbstractCbvControllerResolver implements ControllerResolverInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
     */
    private $resolver;

    /**
     * @param \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface $resolver
     */
    public function __construct(ControllerResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Returns a closure as controller callback.
     * @param string $controllerClass
     * @return \Closure
     */
    abstract protected function createClousure($controllerClass);

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        $controllerClass = $request->attributes->get('_controller');

        if (is_string($controllerClass) && class_exists($controllerClass)) {
            return $this->createClousure($controllerClass);
        }

        return $this->resolver->getController($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments(Request $request, $controller)
    {
        return $this->resolver->getArguments($request, $controller);
    }
}
