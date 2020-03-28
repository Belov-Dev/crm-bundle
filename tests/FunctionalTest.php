<?php

namespace A2Global\CRMBundle\Test;

use A2Global\CRMBundle\A2CRMBundle;
use A2Global\CRMBundle\MySuperService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new A2CRMTestingKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $mySuperService = $container->get('a2crm.my.super.service');
        $this->assertInstanceOf(MySuperService::class, $mySuperService);
        $this->assertIsScalar($mySuperService->getDate());
    }

}

class A2CRMTestingKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new A2CRMBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}