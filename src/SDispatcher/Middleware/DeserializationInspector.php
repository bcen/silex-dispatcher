<?php
namespace SDispatcher\Middleware;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class DeserializationInspector extends AbstractKernelRequestEventListener
{
    /**
     * {@inheritdoc}
     */
    public function doKernelRequest(Request $request)
    {
        $contentType = $request->headers->get('Content-Type', null, true);
        $content = $request->getContent();

        if ($contentType === 'application/json') {
            $data = @json_decode($content, true);
            $request->request = new ParameterBag((array)$data);
        } elseif ($contentType === 'application/xml') {
            $data = $content ? (array)@simplexml_load_string(
                $content, 'SimpleXMLElement', LIBXML_NOCDATA) : null;
            $request->request = new ParameterBag((array)$data);
        }
    }
}
