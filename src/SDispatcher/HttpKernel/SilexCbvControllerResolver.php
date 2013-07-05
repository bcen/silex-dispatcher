<?php
namespace SDispatcher\HttpKernel;

use Silex\Application;
use Silex\ControllerResolver as SilexControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

final class SilexCbvControllerResolver extends SilexControllerResolver
{
    /**
     * @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
     */
    private $resolver;

    /**
     * @var
     */
    private $request;

    public function __construct(Application $app, ControllerResolverInterface $resolver)
    {
        parent::__construct($app);
        $this->resolver = $resolver;
    }

    public function getController(Request $request)
    {
        $this->request = $request;
        try {
            $controller = $this->resolver->getController($request);
        } catch (\Exception $ex) {
            $controller = parent::getController($request);
        }

        return $controller;
    }

    protected function createController($controller)
    {
        if (is_string($controller) && class_exists($controller)) {
            $methodHandler = strtolower($this->request->getMethod());
            $method = null;

            if (method_exists($controller, 'handleRequest')) {
                $method = 'handleRequest';
            } elseif (method_exists($controller, $methodHandler)) {
                $method = $methodHandler;
            } elseif (method_exists($controller, 'handleMissingMethod')) {
                $method = 'handleMissingMethod';
            } else {
                throw new \LogicException('No method handler found');
            }

            if (is_subclass_of($controller, 'SDispatcher\\Common\\RequiredServiceMetaProviderInterface')
                || method_exists($controller, 'getRequiredServices')) {
                $svcIds = (array)call_user_func("{$controller}::getRequiredServices");
                $reflection = new \ReflectionClass($controller);

                if ($reflection->getConstructor()) {
                    $deps = array();
                    foreach ($svcIds as $id) {
                        $deps[] = $this->app[$id];
                    }
                    $c = $reflection->newInstanceArgs($deps);
                } else {
                    $c = $reflection->newInstanceWithoutConstructor();
                }

            } else {
                $c = new $controller();
            }

            return array($c, $method);
        }

        return parent::createController($controller);
    }
}
