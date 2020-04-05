<?php

namespace A2Global\CRMBundle\Tests\Unit;

use A2Global\CRMBundle\Provider\HeartbeatProvider;
use PHPUnit\Framework\TestCase;

class HeartbeatProviderTest extends TestCase
{
    public function testHeartbeatProvider()
    {
        $heartbeatProvider = new HeartbeatProvider();
        $timestamp = $heartbeatProvider->getTimestamp();
        $this->assertNotEmpty($timestamp);
        $this->assertIsScalar($timestamp);
        $this->assertStringContainsString('GMT', $timestamp);
        $this->assertStringContainsString(date('D, d M Y'), $timestamp);
    }
}