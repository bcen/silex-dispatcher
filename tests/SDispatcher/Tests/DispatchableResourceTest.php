<?php
namespace SDispatcher\Tests;

use Symfony\Component\HttpFoundation\Request;

class DispatchableResourceTest extends \PHPUnit_Framework_TestCase
{
    public function requestHandlerProvider()
    {
        return array(
            array('get'),
            array('post'),
            array('put'),
            array('delete')
        );
    }

    protected function createRequest($url = 'http://localhost/')
    {
        $request = Request::create($url);
        $request->headers->set('Accept', '*/*');
        return $request;
    }

    protected function createDispatchableResourceMock(array $excludes = array(), array $includes = array())
    {
        $mockedMethods = array(
            'doContentNegotiationCheck',
            'doMethodAccessCheck',
            'doAuthenticationCheck',
            'doAuthorizationCheck',
            'get',
            'post',
            'put',
            'delete'
        );
        $mockedMethods = array_filter($mockedMethods, function ($method) use ($excludes) {
            if (in_array($method, $excludes)) {
                return false;
            }
            return true;
        });
        $mockedMethods = array_merge($mockedMethods, $includes);
        $controller = $this->getMock('SDispatcher\\DispatchableResource', $mockedMethods);
        return $controller;
    }

    /**
     * @test
     */
    public function doDispatch_should_return_406_response_when_content_type_is_not_acceptable()
    {
        $request = $this->createRequest();
        $request->headers->set('Accept', 'text/html');
        $controller = $this->createDispatchableResourceMock(array('doContentNegotiationCheck'));
        $response = $controller->doDispatch($request);
        $this->assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_should_invoke_doMethodAccessCheck_when_acceptable_content_type_found_in_query_string()
    {
        $request = $this->createRequest('/?format=application/json');
        $request->headers->set('Accept', 'text/html');
        $controller = $this->createDispatchableResourceMock(array('doContentNegotiationCheck'));
        $controller->expects($this->once())
            ->method('doMethodAccessCheck');
        $controller->doDispatch($request);
    }

    /**
     * @test
     */
    public function doDispatch_should_invoke_doMethodAccessCheck_when_acceptable_content_type_found_in_accept_header()
    {
        $request = $this->createRequest();
        $request->headers->set('Accept', 'application/json');
        $controller = $this->createDispatchableResourceMock(array('doContentNegotiationCheck'));
        $controller->expects($this->once())
            ->method('doMethodAccessCheck');
        $controller->doDispatch($request);
    }

    /**
     * @test
     */
    public function doDispatch_should_return_405_response_for_not_allowed_method()
    {
        $request = $this->createRequest();
        $request->setMethod('OPTIONS');
        $controller = $this->createDispatchableResourceMock(array('doMethodAccessCheck'));
        $response = $controller->doDispatch($request);
        $this->assertEquals(405, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_should_invoke_doAuthenticationCheck_when_method_is_allowed()
    {
        $request = $this->createRequest();
        $controller = $this->createDispatchableResourceMock();
        $controller->expects($this->once())
            ->method('doAuthenticationCheck');
        $controller->doDispatch($request);
    }

    /**
     * @test
     */
    public function doDispatch_should_invoke_doAuthorizationCheck_when_request_is_authenticated()
    {
        $request = $this->createRequest();
        $controller = $this->createDispatchableResourceMock();
        $controller->expects($this->once())
            ->method('doAuthorizationCheck');
        $controller->doDispatch($request);
    }

    /**
     * @test
     * @dataProvider requestHandlerProvider
     */
    public function doDispatch_should_invoke_request_handler_when_request_is_authorized($requestHandler)
    {
        $request = $this->createRequest();
        $request->setMethod(strtoupper($requestHandler));
        $controller = $this->createDispatchableResourceMock();
        $controller->expects($this->once())
            ->method($requestHandler);
        $controller->doDispatch($request);
    }

    /**
     * @test
     */
    public function doDispatch_for_GET_request_and_no_routeSegments_should_invoke_readList()
    {
        $request = $this->createRequest();
        $controller = $this->createDispatchableResourceMock(array('get'), array('readList'));
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
        $request = $this->createRequest();
        $controller = $this->createDispatchableResourceMock(array('get'), array('readDetail'));
        $controller->expects($this->once())
            ->method('readDetail');
        $controller->doDispatch($request, array('id'));
    }

    /**
     * @test
     */
    public function doDispatch_for_GET_request_with_schema_as_first_routeSegments_should_invoke_readSchema()
    {
        $request = $this->createRequest();
        $controller = $this->createDispatchableResourceMock(array('get'), array('readSchema'));
        $controller->expects($this->once())
            ->method('readSchema');
        $controller->doDispatch($request, array('schema'));
    }

    /**
     * @test
     */
    public function doDispatch_for_PUT_request_without_routeSegments_should_invoke_updateList()
    {
        $request = $this->createRequest();
        $request->setMethod('PUT');
        $controller = $this->createDispatchableResourceMock(array('put'), array('updateList'));
        $controller->expects($this->once())
            ->method('updateList');
        $controller->doDispatch($request);
    }

    /**
     * @test
     */
    public function doDispatch_for_PUT_request_with_routeSegments_should_invoke_updateDetail()
    {
        $request = $this->createRequest();
        $request->setMethod('PUT');
        $controller = $this->createDispatchableResourceMock(array('put'), array('updateDetail'));
        $controller->expects($this->once())
            ->method('updateDetail');
        $controller->doDispatch($request, array('1'));
    }

    /**
     * @test
     */
    public function doDispatch_for_DELETE_request_without_routeSegments_should_invoke_deleteList()
    {
        $request = $this->createRequest();
        $request->setMethod('DELETE');
        $controller = $this->createDispatchableResourceMock(array('delete'), array('deleteList'));
        $controller->expects($this->once())
            ->method('deleteList');
        $controller->doDispatch($request);
    }

    /**
     * @test
     */
    public function doDispatch_for_DELETE_request_with_routeSegments_should_invoke_deleteDetail()
    {
        $request = $this->createRequest();
        $request->setMethod('DELETE');
        $controller = $this->createDispatchableResourceMock(array('delete'), array('deleteDetail'));
        $controller->expects($this->once())
            ->method('deleteDetail');
        $controller->doDispatch($request, array(1));
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
