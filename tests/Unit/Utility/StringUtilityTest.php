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

    public function testPluralize()
    {
        $rules = [
            ['sample book', 'sample books'],
            ['ability', 'abilities'],
            ['abuse', 'abuses'],
            ['acceptancecriterion', 'acceptancecriteria'],
            ['basis', 'bases'],
            ['bison', 'bison'],
            ['borghese', 'borghese'],
            ['box', 'boxes'],
            ['bream', 'bream'],
        ];

        foreach($rules as $rule){
            $this->assertEquals($rule[1], StringUtility::pluralize($rule[0]));
        }
    }

    public function testSingularize()
    {
        $rules = [
            ['sample book', 'sample books'],
            ['ability', 'abilities'],
            ['abuse', 'abuses'],
            ['acceptancecriterion', 'acceptancecriteria'],
            ['basis', 'bases'],
            ['bison', 'bison'],
            ['borghese', 'borghese'],
            ['box', 'boxes'],
            ['bream', 'bream'],
        ];

        foreach($rules as $rule){
            $this->assertEquals($rule[0], StringUtility::singularize($rule[1]));
        }
    }
}