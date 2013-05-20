<?php
namespace SDispatcher;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use SDispatcher\ControllerMethodDispatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * Resolves the given controller class string into closure.
 */
final class SilexCbvControllerResolver extends AbstractCbvControllerResolver
{
    /**
     * @var \SDispatcher\ControllerMethodDispatcher
     */
    private $methodDispatcher;

    public function __construct(ControllerResolverInterface $resolver, ControllerMethodDispatcher $methodDispatcher)
    {
        parent::__construct($resolver);
        $this->methodDispatcher = $methodDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function createClousure($controllerClass)
    {
        $methodDispatcher = $this->methodDispatcher;
        $self = $this;
        return function (Request $request, Application $app) use (
            $controllerClass,
            $methodDispatcher,
            $self
        ) {
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

            return $methodDispatcher->dispatch($request, $c, $self);
        };
    }
}
