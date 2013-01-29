<?php
namespace SDispatcher\Tests\Common;

use SDispatcher\Common\InMemoryPaginator;
use Symfony\Component\HttpFoundation\Request;

class InMemoryPaginatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function paginate_should_accept_6_arguments()
    {
        $req = Request::create('/');
        $paginator = new InMemoryPaginator();
        $paginator->paginate($req, array(1, 2), 0, 20, 'objects', 'meta');
    }

    /**
     * @test
     */
    public function paginate_should_return_array_of_2_elements()
    {
        $req = Request::create('/');
        $paginator = new InMemoryPaginator();
        list($headers, $data) = $paginator->paginate($req, array(1, 2));
        $this->assertNotNull($data);
        $this->assertNotNull($headers);
    }

    /**
     * @test
     */
    public function meta_container_should_contain_offset_limit_total_prevLink_and_nextLink()
    {
        $req = Request::create('/');
        $paginator = new InMemoryPaginator();
        list($headers, $data) = $paginator->paginate($req, array(1, 2));

        $this->assertArrayHasKey('offset', $data['meta']);
        $this->assertArrayHasKey('limit', $data['meta']);
        $this->assertArrayHasKey('total', $data['meta']);
        $this->assertArrayHasKey('prevLink', $data['meta']);
        $this->assertArrayHasKey('nextLink', $data['meta']);
    }

    /**
     * @test
     */
    public function paginate_should_able_to_change_container_name()
    {
        $req = Request::create('/');
        $paginator = new InMemoryPaginator();
        list($headers, $data) = $paginator->paginate($req, array(1, 2), 0, 20, 'metadata');
        $this->assertArrayHasKey('metadata', $data);

        list($headers, $data) = $paginator->paginate($req, array(1, 2), 0, 20, 'meta', 'items');
        $this->assertArrayHasKey('items', $data);
    }

    /**
     * @test
     */
    public function paginate_should_get_limit_from_query_string()
    {
        $paginator = new InMemoryPaginator();
        $req = Request::create('/?limit=1');
        list($headers, $data) = $paginator->paginate($req, array(1, 2, 3, 4));
        $this->assertEquals(array(1), $data['objects']);

        $req = Request::create('/?limit=2');
        list($headers, $data) = $paginator->paginate($req, array(1, 2, 3, 4));
        $this->assertEquals(array(1, 2), $data['objects']);
    }

    /**
     * @test
     */
    public function prevLink_should_be_null_if_limit_minus_offset_is_lte_0()
    {
        $paginator = new InMemoryPaginator();
        $req = Request::create('http://domain.com/?offset=0&limit=1');
        list($headers, $data) = $paginator->paginate($req, array(1, 2, 3, 4));

        $this->assertEquals(null, $data['meta']['prevLink']);
    }

    /**
     * @test
     */
    public function nextLink_should_be_null_if_limit_plus_offset_is_lt_total()
    {
        $paginator = new InMemoryPaginator();
        $req = Request::create('http://domain.com/?offset=3&limit=1');
        list($headers, $data) = $paginator->paginate($req, array(1, 2, 3, 4));

        $this->assertEquals(null, $data['meta']['nextLink']);
    }

    /**
     * @test
     */
    public function paginate_should_return_data_with_prevLink_if_exists()
    {
        $paginator = new InMemoryPaginator();
        $req = Request::create('http://domain.com/?offset=1&limit=1');
        list($headers, $data) = $paginator->paginate($req, array(1, 2, 3, 4));

        $this->assertEquals('http://domain.com/?limit=1&offset=0', $data['meta']['prevLink']);
    }

    /**
     * @test
     */
    public function paginate_should_return_data_with_nextLink_if_exists()
    {
        $paginator = new InMemoryPaginator();
        $req = Request::create('http://domain.com/?offset=1&limit=1');
        list($headers, $data) = $paginator->paginate($req, array(1, 2, 3, 4));

        $this->assertEquals('http://domain.com/?limit=1&offset=2', $data['meta']['nextLink']);
    }
}
