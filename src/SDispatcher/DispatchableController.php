<?php
namespace SDispatcher;

use ReflectionMethod;
use ReflectionException;
use Twig_Environment;
use Twig_Loader_String;
use SDispatcher\TemplateEngine\TemplateRendererAwareInterface;
use SDispatcher\TemplateEngine\TemplateRendererInterface;
use SDispatcher\TemplateEngine\TwigRendererAdapter;
use SDispatcher\Exception\DispatchingErrorException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class DispatchableController implements DispatchableInterface,
                                                 TemplateRendererAwareInterface
{
    /**
     * Used to render template.
     * @var \SDispatcher\TemplateEngine\TemplateRendererInterface
     */
    private $renderer;

    /**
     * {@inheritdoc}
     */
    public function doDispatch(Request $request, array $routeSegments = array())
    {
        $args = array_values($routeSegments);
        $argc = array_unshift($args, $request);

        $requestMethod = $request->getMethod();
        $requestMethodHandler = strtolower($request->getMethod());

        try {
            $methodReflector = new ReflectionMethod(
                $this, $requestMethodHandler
            );
        } catch (ReflectionException $ex) {
            try {
                $requestMethodHandler =
                    'default' . ucfirst(strtolower($requestMethod));
                $methodReflector = new ReflectionMethod(
                    $this, $requestMethodHandler
                );
            } catch (ReflectionException $ex) {
                throw new DispatchingErrorException(
                    "No request method handler defined for $requestMethod",
                    0,
                    $ex
                );
            }
        }

        if ($argc > $methodReflector->getNumberOfParameters()) {
            throw new DispatchingErrorException(
                "Incorrect number of route arguments, expected $argc arguments"
            );
        }

        $response = call_user_func_array(
            array($this, $requestMethodHandler),
            $args
        );

        return $response;
    }

    /**
     * Default GET request handler.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function defaultGet(Request $request)
    {
        return $this->renderView($this->getContextData($request));
    }

    /**
     * Returns the context data for template.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return mixed
     */
    protected function getContextData(Request $request)
    {
        return array();
    }

    /**
     * Returns the template to be render.
     * @return mixed
     */
    protected function getTemplate()
    {
        $template = '';
        if (property_exists($this, 'template')) {
            $template = $this->{'template'};
        }
        return $template;
    }

    /**
     * Returns a 200 response by default with contents from template.
     * @param array $data
     * @param int $statusCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderView($data = array(), $statusCode = 200)
    {
        return Response::create(
            $this->getRenderer()->render($this->getTemplate(), $data),
            $statusCode
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderer()
    {
        if (!$this->renderer) {
            $this->renderer = new TwigRendererAdapter(
                new Twig_Environment(new Twig_Loader_String())
            );
        }
        return $this->renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function setRenderer(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }
}
