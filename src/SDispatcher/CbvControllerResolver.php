<?php
namespace SDispatcher;

use Silex\Application;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Resolves the given controller class string into closure.
 */
final class CbvControllerResolver implements ControllerResolverInterface
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
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        $controllerClass = $request->attributes->get('_controller');

        if (is_string($controllerClass) && class_exists($controllerClass)) {
            return function (Request $request, Application $app) use ($controllerClass) {
                if (is_subclass_of($controllerClass, 'SDispatcher\\Common\\RequiredServiceMetaProviderInterface')) {
                    $svcIds = (array)call_user_func("{$controllerClass}::getRequiredServices");
                    $reflection = new \ReflectionClass($controllerClass);

                    if ($reflection->getConstructor()) {
                        $deps = array();
                        foreach ($svcIds as $id) {
                            $deps[] = $app[$id];
                        }
                        $c = $reflection->newInstanceArgs($deps);
                    } else {
                        $c = $reflection->newInstanceWithoutConstructor();
                    }

                } else {
                    $c = new $controllerClass();
                }

                if (method_exists($c, 'dispatch')) {
                    return $c->{'dispatch'}($request);
                }

                return AbstractCbvController::doDispatch($c, $request);
            };
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
