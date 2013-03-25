<?php
namespace SDispatcher\TemplateEngine;

use Twig_Environment;

/**
 * @deprecated
 */
class TwigRendererAdapter implements TemplateRendererInterface
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * Constructor.
     * @param \Twig_Environment $twig
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->setTwig($twig);
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, $contextData = array())
    {
        return $this->getTwig()->render($template, $contextData);
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * @param \Twig_Environment $twig
     */
    public function setTwig(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }
}
