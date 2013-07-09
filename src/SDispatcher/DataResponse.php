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

    public static function ok($content, $headers = array())
    {
        return new static($content, 200, $headers);
    }

    public static function created($content, $headers = array())
    {
        return new static($content, 201, $headers);
    }

    public static function accepted($content, $headers = array())
    {
        return new static($content, 202, $headers);
    }

    public static function noContent($headers = array())
    {
        return new static('', 204, $headers);
    }

    public static function badRequest($content, $headers = array())
    {
        return new static($content, 400, $headers);
    }

    public static function unauthorized($content, $headers = array())
    {
        return new static($content, 401, $headers);
    }

    public static function forbidden($content, $headers = array())
    {
        return new static($content, 403, $headers);
    }

    public static function notFound($content, $headers = array())
    {
        return new static($content, 404, $headers);
    }

    public static function methodNotAllowed($content, $headers = array())
    {
        return new static($content, 405, $headers);
    }

    public static function notAcceptable($content, $headers = array())
    {
        return new static($content, 406, $headers);
    }

    public static function conflict($content, $headers = array())
    {
        return new static($content, 409, $headers);
    }
}
