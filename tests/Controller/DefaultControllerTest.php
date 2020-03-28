<?php

namespace A2Global\CRMBundle\Tests;

use A2Global\CRMBundle\A2CRMBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class DefaultControllerTest extends TestCase
{
    public function testIndex()
    {
        $kernel = new A2CRMControllerKernel();
        $client = new Client($kernel);
        $client->request('GET', '/heartbeat');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('OK', $client->getResponse()->getContent());
        $this->assertContains('GMT', $client->getResponse()->getContent());
    }
}

class A2CRMControllerKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles()
    {
        return [
            new A2CRMBundle(),
            new FrameworkBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import(__DIR__.'/../../src/Resources/config/routes.test.yaml');
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader)
    {
        $containerBuilder->loadFromExtension('framework', [
            'secret' => 'F00',
        ]);
    }

    public function getCacheDir()
    {
        return __DIR__.'/../cache/'.spl_object_hash($this);
    }
}
