<?php
namespace SDispatcher;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Resolves the given controller class string into closure.
 */
final class SilexCbvControllerResolver extends AbstractCbvControllerResolver
{
    /**
     * {@inheritdoc}
     */
    protected function createClousure($controllerClass)
    {
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
}
