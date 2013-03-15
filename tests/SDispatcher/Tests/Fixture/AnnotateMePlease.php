<?php
namespace SDispatcher\Tests\Fixture;

use SDispatcher\Common\Annotation\SupportedFormats;
use SDispatcher\Common\Annotation\ResourceIdentifier;
use SDispatcher\Common\Annotation\PageLimit;
use SDispatcher\Common\Annotation\WillPaginate;
use SDispatcher\DataResponse;

/**
 * @SupportedFormats({"application/json", "application/xml"})
 * @ResourceIdentifier("my_resource_identifier")
 * @PageLimit(100)
 */
class AnnotateMePlease
{
    /**
     * @return string
     */
    public function method1()
    {
        return 'method1';
    }

    /**
     * @SupportedFormats("application/xml")
     * @return string
     */
    public function method2()
    {
        return 'method2';
    }

    /**
     * @return string
     * @PageLimit(10)
     */
    public function method3()
    {
        return 'method3';
    }

    /**
     * @WillPaginate
     * @SupportedFormats("application/json")
     */
    public function method4()
    {
        return 'method4';
    }

    /**
     * @WillPaginate
     * @SupportedFormats("application/json")
     */
    public function method5()
    {
        return new DataResponse(array(
            'name' => 'method5'
        ));
    }

    /**
     * @WillPaginate
     * @SupportedFormats("application/xml")
     */
    public function method6()
    {
        return new DataResponse(array(
            'name' => 'method6'
        ));
    }

    public function method7()
    {
        return array(
            'name' => 'method7'
        );
    }
}
