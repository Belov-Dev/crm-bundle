<?php

namespace A2Global\CRMBundle\EventListener;

use A2Global\CRMBundle\Loader\ProxyClassesLoader;

class KernelRequestEventListener
{
    private $proxyClassesLoader;

    public function __construct(ProxyClassesLoader $proxyClassesLoader)
    {
        $this->proxyClassesLoader = $proxyClassesLoader;
    }

    public function onKernelRequest()
    {
//        $this->proxyClassesLoader->load();
    }
}