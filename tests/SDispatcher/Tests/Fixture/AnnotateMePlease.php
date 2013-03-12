<?php
namespace SDispatcher\Tests\Fixture;

use SDispatcher\Common\Annotation\SupportedFormats;

/**
 * @SupportedFormats("application/json")
 */
class AnnotateMePlease
{
    public function method1()
    {
        return 'method1';
    }

    public function method2()
    {
        return 'method2';
    }
}
