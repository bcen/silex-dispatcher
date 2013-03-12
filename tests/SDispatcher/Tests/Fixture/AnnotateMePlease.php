<?php
namespace SDispatcher\Tests\Fixture;

use SDispatcher\Common\Annotation\SupportedFormats;
use SDispatcher\Common\Annotation\ResourceIdentifier;
use SDispatcher\Common\Annotation\PageLimit;
use SDispatcher\Common\Annotation\WillPaginate;

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
     */
    public function method2()
    {
        return 'method2';
    }

    public function method3()
    {
    }

    /**
     * @WillPaginate
     */
    public function method4()
    {
    }
}
