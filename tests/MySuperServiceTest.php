<?php

namespace A2Global\CRMBundle\Test;

class MySuperServiceTest extends TestCase
{
    public function testGetDate()
    {
        $myService = new MySuperService();
        $date = $myService->getDate();
        $this->assertString($date);
    }
}