<?php
namespace SDispatcher;

use SDispatcher\DispatchingServiceProvider;
use SDispatcher\DispatchableInterface;
use SDispatcher\TemplateEngine\TemplateRendererAwareInterface;
use SDispatcher\Common\ClassResolver;
use SDispatcher\Exception\DispatchingErrorException;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Used to create a closure controller for {@link \Silex\Application} route.
 */
class ControllerFactory
{
    /**
     * Used to create controller and resolves its dependencies.
     * @var \SDispatcher\Common\ClassResolver
     */
    protected $resolver;

    /**
     * Creates an instance with the specified $resolver.
     * @param \SDispatcher\Common\ClassResolver $resolver
     */
    public function __construct(ClassResolver $resolver)
    {
        $this->resolver= $resolver;
        $this->setupTemplateEngine();
    }

    /**
     * Setups the resolver for {@link \SDispatcher\TemplateEngine\TemplateRendererAwareInterface} injection.
     */
    protected function setupTemplateEngine()
    {
        $this->resolver->onFinish(function(array $containers, $object) {
            if (!$object instanceof TemplateRendererAwareInterface) {
                return;
            }
            foreach ($containers as $container) {
                if (isset($container[DispatchingServiceProvider::TEMPLATE_RENDERER])) {
                    $object->setRenderer(
                        $container[DispatchingServiceProvider::TEMPLATE_RENDERER]
                    );
                }
            }
        });
    }

    /**
     * Make route for $delegate with given $pattern and $controllerClass.
     * @param \Silex\Application|\Silex\ControllerCollection $delegate
     * @param string $pattern
     * @param string $controllerClass
     * @param string $method
     * @return mixed
     * @throws \LogicException When $delegate does not implement "match"
     */
    public function makeRoute($delegate,
                              $pattern,
                              $controllerClass,
                              $method = 'GET|POST|PUT|DELETE')
    {
        if (!method_exists($delegate, 'match')) {
            throw new \LogicException(
                '$delegate must implement "match" method.'
            );
        }

        preg_match_all('/{(.*?)}/', $pattern, $matches);

        return $delegate->match(
            $pattern,
            $this->createClosure(
                $controllerClass,
                isset($matches[1]) ? $matches[1] : array()
            )
        )->method($method);
    }

    /**
     * Used to create a closure controller for Silex route with the specified
     * $controllerClass and the $routeSegmentName.
     * @param string $controllerClass The controller class to instantiate
     * @param array $routeSegmentName The available dynamic route variable names
     * @throws \SDispatcher\Exception\DispatchingErrorException
     * @return \Symfony\Component\HttpFoundation\Response $response
     */
    public function createClosure($controllerClass,
                                  array $routeSegmentName = array())
    {
        $resolver = $this->resolver;
        return function(Application $app, Request $request) use(
            $controllerClass,
            $routeSegmentName,
            $resolver
        ) {
            $routeSegments = array();
            foreach ($routeSegmentName as $name) {
                $routeSegments[$name] = $request->attributes->get($name);
            }

            $response = null;
            $exception = null;

            try {
                $controller = $resolver->create($controllerClass);
                if (!$controller instanceof DispatchableInterface) {
                    throw new DispatchingErrorException(
                        "\"$controllerClass\" must implements " .
                            "DispatchableInterface"
                    );
                }

                $response = $controller->doDispatch($request, $routeSegments);
                if (!$response instanceof Response) {
                    throw new DispatchingErrorException(
                        "\"$response\" must extends " .
                            "Symfony\\Component\\HttpFoundation\\Response"
                    );
                }
            } catch (DispatchingErrorException $ex) {
                $exception = $ex;
            }

            if ($exception) {
                if ($app['debug']) {
                    throw $exception;
                } else {
                    $app->abort(500, $exception->getMessage());
                }
            }

            return $response;
        };
    }

    /**
     * @See createClosure()
     * @param string $controllerClass
     * @param array $routeSegmentName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke($controllerClass,
                             array $routeSegmentName = array())
    {
        return $this->createClosure($controllerClass, $routeSegmentName);
    }
}
