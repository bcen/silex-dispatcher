<?php
namespace SDispatcher;

use Symfony\Component\HttpFoundation\Response;

class DataResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }
}
