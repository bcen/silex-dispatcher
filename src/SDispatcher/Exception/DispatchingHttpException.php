<?php
namespace SDispatcher\Exception;

use Symfony\Component\HttpFoundation\Response;

class DispatchingHttpException extends DispatchingErrorException
{
    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    private $response;

    public function __construct(Response $response, $code = 0, $exception = null)
    {
        $this->setResponse($response);
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}
