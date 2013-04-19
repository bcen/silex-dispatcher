<?php
namespace SDispatcher\Common;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

class DefaultXmlEncoder implements EncoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = array())
    {
        // stolen from codeigniter-restserver
        // https://github.com/philsturgeon/codeigniter-restserver/blob/master/application/libraries/Format.php#L87
        $toXml = function(
            $data = null,
            $structure = null,
            $basenode = 'response'
        ) use(&$toXml) {
            if (ini_get('zend.ze1_compatibility_mode') == 1) {
                ini_set('zend.ze1_compatibility_mode', 0);
            }
            if ($structure === null) {
                $structure = simplexml_load_string(
                    "<?xml version='1.0' encoding='utf-8'?><$basenode />");
            }

            if (!is_array($data) && !is_object($data)) {
                $data = (array)$data;
            }

            foreach ($data as $k => $v) {
                if (is_bool($v)) {
                    $v = $v ? 'true' : 'false';
                }

                if (is_numeric($k)) {
                    $k = 'item';
                }

                $k = preg_replace('/[^a-z_\-0-9]/i', '', $k);

                if (is_array($v) || is_object($v)) {
                    $node = $structure->addChild($k);
                    $toXml($v, $node, $k);
                } else {
                    $v = htmlspecialchars(
                        html_entity_decode($v, ENT_QUOTES, 'UTF-8'),
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    $structure->addChild($k, $v);
                }
            }

            return $structure->asXML();
        };

        return $toXml($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return $format === 'xml';
    }
}
