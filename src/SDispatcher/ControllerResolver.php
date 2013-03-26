<?php
namespace SDispatcher;

use Silex\Application;
use Silex\ControllerResolver as SilexControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * Extends the base resolver to include resovling dependency from container by
 * class name or argument name.
 */
class ControllerResolver extends SilexControllerResolver
{
    /**
     * @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
     */
    protected $resolver;

    /**
     * @var \Silex\Application
     */
    protected $app;

    public function __construct(ControllerResolverInterface $resolver, Application $app)
    {
        $this->resolver = $resolver;
        $this->app = $app;
        parent::__construct($app, null);
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        try {
            $controller = parent::getController($request);
        } catch (\LogicException $ex) {
            $controller = $this->resolver->getController($request);
        }
        return $controller;
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments(Request $request, $controller)
    {
        try {
            $args = parent::getArguments($request, $controller);
        } catch (\RuntimeException $ex) {
            $args = $this->resolver->getArguments($request, $controller);
        }
        return $args;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetArguments(Request $request, $controller, array $parameters)
    {
        /* @var \ReflectionClass|\ReflectionParameter $p */
        foreach ($parameters as $p) {
            $name = $p->getName();
            if (isset($this->app[$name])) {
                $request->attributes->set($name, $this->app[$name]);
            } elseif ($p->getClass()) {
                /* @var \ReflectionClass $reflectedClass */
                $reflectedClass = $p->getClass();
                $className = $reflectedClass->getName();
                if (isset($this->app[$className])) {
                    $request->attributes->set($name, $this->app[$className]);
                }
            }
        }

        return parent::doGetArguments($request, $controller, $parameters);
    }
}
