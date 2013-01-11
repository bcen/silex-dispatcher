<?php
namespace SDispatcher\TemplateEngine;

interface TemplateRendererAwareInterface
{
    /**
     * Returns the renderer.
     * @return mixed
     */
    public function getRenderer();

    /**
     * Sets the renderer
     * @param \SDispatcher\TemplateEngine\TemplateRendererInterface $renderer
     * @returns mixed
     */
    public function setRenderer(TemplateRendererInterface $renderer);
}
