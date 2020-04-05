<?php

namespace A2Global\CRMBundle\Tests\Unit\Utility;

use A2Global\CRMBundle\Utility\StringUtility;
use PHPUnit\Framework\TestCase;

class StringUtilityTest extends TestCase
{
    public function testNormalize()
    {
        $this->assertEquals('Sample book', StringUtility::normalize(' * % sample Book&^%$#@!!'));
    }

    public function testCamelCase()
    {
        $this->assertEquals('sampleBook', StringUtility::toCamelCase('Sample!@#$%^&*()book'));
    }

    public function testSnakeCase()
    {
        $this->assertEquals('sample_book_anywhere', StringUtility::toSnakeCase('!@#Sample!@#book!@#anywhere!@#'));
    }

    public function testPascalCase()
    {
        $this->assertEquals('SampleBookClass', StringUtility::toPascalCase('!@#sample!@#BOOK!@#class!@#'));
    }
}