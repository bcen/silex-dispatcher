<?php
namespace SDispatcher\Tests;

use Symfony\Component\HttpFoundation\Request;

class DispatchableResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function doDispatch_should_return_406_response_when_content_type_is_not_acceptable()
    {
        $request = Request::create('http://localhost/');
        $request->headers->set('Accept', 'application/vnd-some-format');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readList'));

        $response = $controller->doDispatch($request);

        $this->assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_should_return_200_response_for_acceptable_content_type_from_query_string()
    {
        $request = Request::create('http://localhost/?format=application/json');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readList'));
        $controller->expects($this->once())
            ->method('readList')
            ->will($this->returnValue(array()));

        $response = $controller->doDispatch($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_should_return_200_response_for_acceptable_content_type_from_accept_header()
    {
        $request = Request::create('http://localhost/');
        $request->headers->set('Accept', 'application/json');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readList'));
        $controller->expects($this->once())
            ->method('readList')
            ->will($this->returnValue(array()));

        $response = $controller->doDispatch($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_should_return_200_response_for_acceptable_content_type_from_asterisk_accept_header()
    {
        $request = Request::create('http://localhost/');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readList'));
        $controller->expects($this->once())
            ->method('readList')
            ->will($this->returnValue(array()));

        $response = $controller->doDispatch($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_should_return_405_for_not_allowed_method()
    {
        $request = Request::create('http://localhost/', 'POST');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('some'));
        $response = $controller->doDispatch($request);

        $this->assertEquals(405, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_for_GET_request_and_no_routeSegments_should_invoke_readList()
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readList'));
        $controller->expects($this->once())
            ->method('readList')
            ->will($this->returnValue(array()));

        $controller->doDispatch($request);
    }

    /**
     * @test
     */
    public function doDispatch_for_GET_request_with_routeSegments_should_invoke_readDetail()
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readDetail'));
        $controller->expects($this->once())
            ->method('readDetail');
        $controller->doDispatch($request, array('0010'));
    }

    /**
     * @test
     */
    public function doDispatch_for_GET_request_with_schema_as_first_routeSegments_should_invoke_readSchema()
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readSchema'));
        $controller->expects($this->once())
            ->method('readSchema');
        $controller->doDispatch($request, array('schema'));
    }

    /**
     * @test
     */
    public function doDispatch_for_PUT_request_without_routeSegments_should_invoke_updateList()
    {
        $request = Request::create('/', 'PUT');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('updateList'));
        $controller->getResourceOption()->setAllowedMethods(array('PUT'));
        $controller->expects($this->once())
            ->method('updateList');
        $controller->doDispatch($request);
    }

    /**
     * @test
     */
    public function doDispatch_for_PUT_request_with_routeSegments_should_invoke_updateDetail()
    {
        $request = Request::create('/', 'PUT');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('updateDetail'));
        $controller->getResourceOption()->setAllowedMethods(array('PUT'));
        $controller->expects($this->once())
            ->method('updateDetail');
        $controller->doDispatch($request, array('10010'));
    }

    /**
     * @test
     */
    public function doDispatch_for_DELETE_request_without_routeSegments_should_invoke_deleteList()
    {
        $request = Request::create('/', 'DELETE');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('deleteList'));
        $controller->getResourceOption()->setAllowedMethods(array('DELETE'));
        $controller->expects($this->once())
            ->method('deleteList');
        $controller->doDispatch($request);
    }

    /**
     * @test
     */
    public function doDispatch_for_DELETE_request_with_routeSegments_should_invoke_deleteDetail()
    {
        $request = Request::create('/', 'DELETE');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('deleteDetail'));
        $controller->getResourceOption()->setAllowedMethods(array('DELETE'));
        $controller->expects($this->once())
            ->method('deleteDetail');
        $controller->doDispatch($request, array(1, 2));
    }

    /**
     * @test
     */
    public function doDispatch_for_readSchema_should_return_json_response_by_default()
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readSchema'));
        $controller->expects($this->once())
            ->method('readSchema')
            ->will($this->returnValue(array(
                'contentType' => 'application/json',
                'pageLimit' => 20
            )));
        $response = $controller->doDispatch($request, array('schema'));

        $expected = '
            {
                "contentType": "application\/json",
                "pageLimit": 20
            }
        ';
        $expected = preg_replace('/\s+/', '', $expected);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals($expected, $response->getContent());
    }

    /**
     * @test
     */
    public function doDispatch_for_readSchema_should_can_return_xml_response_if_configured()
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept', 'application/xml');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readSchema'));
        $controller->getResourceOption()->setSupportedFormats(array('application/xml'));
        $controller->expects($this->once())
            ->method('readSchema')
            ->will($this->returnValue(array(
            'contentType' => 'application/json',
            'pageLimit' => 20
        )));
        $response = $controller->doDispatch($request, array('schema'));

        $expected =
            '<?xml version="1.0" encoding="utf-8"?>' .
            '<response>' .
                '<contentType>application/json</contentType>' .
                '<pageLimit>20</pageLimit>' .
            '</response>';
        $expected = str_replace("\n", '', $expected);
        $actual = str_replace("\n", '', $response->getContent());

        $this->assertEquals('application/xml', $response->headers->get('Content-Type'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function doDispatch_for_readList_should_be_paginated()
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readList'));
        $controller->expects($this->once())
            ->method('readList')
            ->will($this->returnValue(array(
                1, 2, 3
            )));
        $response = $controller->doDispatch($request);

        $expected = '
            {
                "meta": {
                    "offset": 0,
                    "limit": 20,
                    "total": 3
                },
                "objects": [1,2,3]
            }
        ';
        $expected = preg_replace('/\s+/', '', $expected);

        $this->assertEquals(0, $response->headers->get('X-Pagination-Offset'));
        $this->assertEquals(20, $response->headers->get('X-Pagination-Limit'));
        $this->assertEquals(3, $response->headers->get('X-Pagination-Count'));
        $this->assertEquals($expected, $response->getContent());
    }

    /**
     * @test
     */
    public function doDispatch_for_readDetail_with_null_return_should_return_404_response_with_correct_error_message()
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('readDetail'));
        $controller->expects($this->once())
            ->method('readDetail')
            ->will($this->returnValue(null));
        $response = $controller->doDispatch($request, array('1101'));

        $expected = '
            {
                "errorMessage": "Not Found"
            }
        ';
        $expected = preg_replace('/\s+/', '', $expected);
        $actual = preg_replace('/\s+/', '', $response->getContent());

        $this->assertEquals($expected, $actual);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_for_createResource_should_return_response_with_location_header_if_success()
    {
        $number = '2';
        $request = Request::create(
            'http://api.domain.com/numbers/?offset=100&',
            'POST'
        );
        $request->headers->set('Accept', '*/*');

        $controller = $this->getMock('SDispatcher\\DispatchableResource', array('createResource'));
        $controller->getResourceOption()->setAllowedMethods(array('GET', 'POST'));
        $controller->expects($this->once())
            ->method('createResource')
            ->will($this->returnValue(array(
                'id' => $number
            )));

        $response = $controller->doDispatch($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(
            'http://api.domain.com/numbers/' . $number,
            $response->headers->get('Location')
        );
    }
}
