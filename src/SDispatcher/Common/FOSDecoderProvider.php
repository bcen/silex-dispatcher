<?php
namespace SDispatcher\Common;

use FOS\Rest\Decoder\DecoderProviderInterface;
use FOS\Rest\Decoder\JsonDecoder;
use FOS\Rest\Decoder\XmlDecoder;

class FOSDecoderProvider implements DecoderProviderInterface
{
    public function supports($format)
    {
        return in_array($format, array('json', 'xml'));
    }

    public function getDecoder($format)
    {
        if ($format === 'json') {
            return new JsonDecoder();
        } elseif ($format === 'xml') {
            return new XmlDecoder();
        }

        return null;
    }
}
