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
        $controller = $request->attributes->get('_controller');

        if (is_string($controller) && class_exists($controller)) {
            return function (Request $request, Application $app) use ($controller) {
                $c = new $controller();

                if (method_exists($c, 'dispatch')) {
                    return $c->{'dispatch'}($request, $app);
                }

                return AbstractCbvController::doDispatch($c, $request, $app);
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
