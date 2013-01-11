<?php
namespace SDispatcher\Tests\Common;

use SDispatcher\Common\ResourceBundle;
use Symfony\Component\HttpFoundation\Request;

class ResourceBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getData_with_no_arguments_should_return_all_data()
    {
        $bundle = new ResourceBundle(Request::create('/'), array(
            'errorMessage' => 'N/A'
        ));
        $this->assertEquals(array('errorMessage' => 'N/A'), $bundle->getData());
    }

    /**
     * @test
     */
    public function getData_with_key_should_return_value_if_exists()
    {
        $bundle = new ResourceBundle(Request::create('/'), array(
            'errorMessage' => 'N/A'
        ));
        $this->assertEquals('N/A', $bundle->getData('errorMessage'));
    }

    /**
     * @test
     */
    public function getData_with_key_should_return_default_if_not_exists()
    {
        $bundle = new ResourceBundle(Request::create('/'));
        $this->assertEquals('N/A', $bundle->getData('errorMessage', 'N/A'));
    }

    /**
     * @test
     */
    public function addData_should_append_to_array_data()
    {
        $bundle = new ResourceBundle(Request::create('/'), array(
            'errorMessage' => 'N/A'
        ));
        $bundle->addData('errorCode', 0xff);

        $this->assertEquals(
            array('errorMessage' => 'N/A', 'errorCode' => 0xff),
            $bundle->getData()
        );
    }
}
