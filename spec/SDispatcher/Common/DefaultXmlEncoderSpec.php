<?php

namespace spec\SDispatcher\Common;

use PhpSpec\ObjectBehavior;

class DefaultXmlEncoderSpec extends ObjectBehavior
{
    public function it_should_be_initializable()
    {
        $this->shouldHaveType('SDispatcher\Common\DefaultXmlEncoder');
    }

    public function its_supportsEncoding_should_return_true_if_format_is_xml()
    {
        $this->supportsEncoding('xml')->shouldReturn(true);
    }

    public function its_encode_should_return_correct_format_in_xml_string()
    {
        $data = array(
            'message' => 'Hello World'
        );
        $expected = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
                    '<response><message>Hello World</message></response>' . "\n";
        $this->encode($data, 'xml')->shouldReturn($expected);
    }
}
