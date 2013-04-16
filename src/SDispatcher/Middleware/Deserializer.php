<?php
namespace SDispatcher\Middleware;

use FOS\Rest\Decoder\DecoderProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class Deserializer extends AbstractKernelRequestEventListener
{
    /**
     * @var \FOS\Rest\Decoder\DecoderProviderInterface
     */
    private $decoderProvider;

    /**
     * @param \FOS\Rest\Decoder\DecoderProviderInterface $decoderProvider
     */
    public function __construct(DecoderProviderInterface $decoderProvider)
    {
        $this->decoderProvider = $decoderProvider;
    }

    protected function doKernelRequest(Request $request)
    {
        $format = $request->getFormat(
            $request->headers->get('Content-Type', null, true));
        if ($this->decoderProvider->supports($format)) {
            /** @var \FOS\Rest\Decoder\DecoderInterface $decoder */
            $decoder = $this->decoderProvider->getDecoder($format);
            $data = $decoder->decode($request->getContent());
            $request->request->replace(is_array($data) ? $data : array());
        }
    }
}
