<?php
namespace SDispatcher\Common;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated
 */
class ResourceBundle
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    private $response;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $data
     */
    public function __construct(Request $request, $data = null)
    {
        $this->request = $request;
        $this->data = $data;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        if (!$this->response) {
            $this->response = new Response();
        }
        return $this->response;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return \SDispatcher\Common\ResourceBundle
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getData($key = null, $default = null)
    {
        if (!$key) {
            return $this->data;
        }
        if (!is_array($this->data)) {
            return $default;
        }

        return
            (isset($this->data[$key]) || array_key_exists($key, $this->data))
                ? $this->data[$key]
                : $default;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addData($key, $value)
    {
        if (is_array($this->data)) {
            $this->data[$key] = $value;
        }
    }
}
