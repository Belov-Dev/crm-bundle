<?php

namespace A2Global\CRMBundle\Tests\Unit;

use A2Global\CRMBundle\MySuperService;
use PHPUnit\Framework\TestCase;

class MySuperServiceTest extends TestCase
{
    public function testGetDate()
    {
        $mySuperService = new MySuperService();
        $date = $mySuperService->getDate();
        $this->assertNotEmpty($date);
        $this->assertIsScalar($date);
        $this->assertStringContainsString('GMT', $date);
    }
}