<?php
namespace SDispatcher\Tests\Fixture;

use SDispatcher\Common\Annotation\SupportedFormats;

/**
 * @SupportedFormats({"application/json", "application/xml"})
 */
class AnnotateMePlease
{
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
}
