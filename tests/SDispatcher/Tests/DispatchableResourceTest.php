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
    public function detectSupportedContentType_should_detect_query_string_first()
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
    public function detectSupportedContentType_should_prioritize_accept_header_than_query_string()
    {
        $request = Request::create('/?format=application/whatever2');
        $request->headers->set('Accept', 'application/whatever');

        $resource = new DispatchableResourceProxy();
        $resource->getResourceOption()->setSupportedFormats(array('application/whatever'));

        $contentType = $resource->detectSupportedContentType($request);
        $this->assertEquals('application/whatever', $contentType);
    }

    /**
     * @test
     */
    public function detectSupportedContentType_should_use_default_format_if_asterisk_found_in_accept_header()
    {
        $request = Request::create('/');
        $request->headers->set('Accept', '*/*');

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
}
