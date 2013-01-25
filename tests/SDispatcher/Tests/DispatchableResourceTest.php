<?php
namespace SDispatcher\Tests;

use Symfony\Component\HttpFoundation\Request;
use SDispatcher\Tests\DispatchableResourceProxy;
use SDispatcher\Exception\DispatchingHttpException;

class DispatchableResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function detectSupportedContentType_can_detect_from_query_string()
    {
        $request = Request::create('/?format=application/whatever2');

        $resource = new DispatchableResourceProxy();
        $resource->getResourceOption()->setSupportedFormats(array('application/whatever2'));

        $contentType = $resource->detectSupportedContentType($request);
        $this->assertEquals('application/whatever2', $contentType);
    }

    /**
     * @test
     */
    public function detectSupportedContentType_can_detect_from_query_string_with_short_hand_format()
    {
        // json
        $request = Request::create('/?format=json');
        $request->headers->set('Accept', '');

        $resource = new DispatchableResourceProxy();
        $resource->getResourceOption()->setSupportedFormats(array('application/json'));

        $contentType = $resource->detectSupportedContentType($request);
        $this->assertEquals('application/json', $contentType);

        // xml
        $request = Request::create('/?format=xml');
        $request->headers->set('Accept', '');

        $resource = new DispatchableResourceProxy();
        $resource->getResourceOption()->setSupportedFormats(array('application/xml'));

        $contentType = $resource->detectSupportedContentType($request);
        $this->assertEquals('application/xml', $contentType);

        // html
        $request = Request::create('/?format=html');
        $request->headers->set('Accept', '');

        $resource = new DispatchableResourceProxy();
        $resource->getResourceOption()->setSupportedFormats(array('text/html'));

        $contentType = $resource->detectSupportedContentType($request);
        $this->assertEquals('text/html', $contentType);
    }

    /**
     * @test
     */
    public function detectSupportedContentType_can_detect_from_accept_header()
    {
        $request = Request::create('/');
        $request->headers->set('Accept', 'application/whatever2');

        $resource = new DispatchableResourceProxy();
        $resource->getResourceOption()->setSupportedFormats(array('application/whatever2'));

        $contentType = $resource->detectSupportedContentType($request);
        $this->assertEquals('application/whatever2', $contentType);
    }

    /**
     * @test
     */
    public function detectSupportedContentType_should_prioritize_query_string_over_accept_header()
    {
        $request = Request::create('/?format=application/format2');
        $request->headers->set('Accept', 'application/format1');

        $resource = new DispatchableResourceProxy();
        $resource->getResourceOption()->setSupportedFormats(array(
            'application/format1', 'application/format2'
        ));

        $contentType = $resource->detectSupportedContentType($request);
        $this->assertEquals('application/format2', $contentType);
    }

    /**
     * @test
     */
    public function detectSupportedContentType_should_return_null_if_no_content_type_detected_in_request()
    {
        $request = Request::create('/');
        $request->headers->set('Accept', 'application/invalid');
        $resource = new DispatchableResourceProxy();
        $contentType = $resource->detectSupportedContentType($request);
        $this->assertNull($contentType);
    }

    /**
     * @test
     */
    public function detectSupportedContentType_should_use_default_format_if_asterisk_found_in_accept_header()
    {
        $request = Request::create('/');
        $request->headers->set('Accept', 'text/html,*/*');

        $resource = new DispatchableResourceProxy();
        $contentType = $resource->detectSupportedContentType($request);
        $this->assertEquals($resource->getResourceOption()->getDefaultFormat(), $contentType);
    }

    /**
     * @test
     */
    public function doContentNegotiationCheck_should_throw_DispatchingHttpException_if_no_acceptable_content_type_found()
    {
        $request = Request::create('/');
        $request->headers->set('Accept', 'application/invalid');
        $resource = new DispatchableResourceProxy();

        try {
            $resource->doContentNegotiationCheck($request);
            $this->fail('Expected DispatchingHttpException exception');
        } catch (DispatchingHttpException $ex) {
        }
    }

    /**
     * @test
     */
    public function doMethodAccessCheck_should_throw_DispatchingHttpException_if_method_is_not_allowed()
    {
        $request = Request::create('/');
        $resource = new DispatchableResourceProxy();
        $resource->getResourceOption()->setAllowedMethods(array('DELETE'));

        try {
            $resource->doMethodAccessCheck($request);
            $this->fail('Expected DispatchingHttpException exception');
        } catch (DispatchingHttpException $ex) {
        }
    }

    /**
     * @test
     */
    public function doSerialization_can_serialize_to_json()
    {
        $request = Request::create('/');
        $resource = new DispatchableResourceProxy();
        $bundle = $resource->createBundle($request);
        $bundle->setData(array(
            'message' => 'Hello World'
        ));

        $resource->doSerialization($bundle, 'application/json');
        $actual = '{"message":"Hello World"}';
        $expected = $bundle->getData();

        $this->assertEquals($actual, $expected);
        $this->assertEquals($actual, $bundle->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function doSerialization_can_serialize_to_xml()
    {
        $request = Request::create('/');
        $resource = new DispatchableResourceProxy();
        $bundle = $resource->createBundle($request);
        $bundle->setData(array(
            'message' => 'Hello World'
        ));

        $resource->doSerialization($bundle, 'application/xml');
        $actual = preg_replace('/\s+/', '', '
            <?xml version="1.0" encoding="utf-8"?>
            <response>
                <message>Hello World</message>
            </response>
        ');
        $expected = preg_replace('/\s+/', '', $bundle->getData());
        $this->assertEquals($actual, $expected);

        $expected = preg_replace('/\s+/', '', $bundle->getResponse()->getContent());
        $this->assertEquals($actual, $expected);
    }
}
