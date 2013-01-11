<?php
namespace SDispatcher\TemplateEngine;

use Mustache_Engine;

class MustacheRendererAdapter implements TemplateRendererInterface
{
    /**
     * @var \Mustache_Engine
     */
    private $mustache;

    /**
     * Constructor
     * @param \Mustache_Engine $mustache
     */
    public function __construct(Mustache_Engine $mustache)
    {
        $this->setMustache($mustache);
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, $contextData = array())
    {
        return $this->getMustache()->render($template, $contextData);
    }

    /**
     * @return \Mustache_Engine
     */
    public function getMustache()
    {
        return $this->mustache;
    }

    /**
     * @param \Mustache_Engine $mustache
     */
    public function setMustache(Mustache_Engine $mustache)
    {
        $this->mustache = $mustache;
    }
}
