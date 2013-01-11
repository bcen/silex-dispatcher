<?php
namespace SDispatcher\TemplateEngine;

interface TemplateRendererInterface
{
    /**
     * Renders the $template with $contextData.
     * @param mixed $template
     * @param mixed $contextData
     * @return mixed
     */
    public function render($template, $contextData = null);
}
