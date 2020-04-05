<?php

namespace A2Global\CRMBundle\Loader;

use A2Global\CRMBundle\Provider\CacheDirectoryProvider;

class ProxyClassesLoader
{
    protected $cacheDirectoryProvider;

    public function __construct(CacheDirectoryProvider $cacheDirectoryProvider)
    {
        $this->cacheDirectoryProvider = $cacheDirectoryProvider;
    }

    public function load()
    {
        foreach (glob($this->cacheDirectoryProvider->get() . '/*') as $proxyClass) {
            if (!class_exists('App\\Entity\\' . ucfirst($proxyClass))) {
                require_once $proxyClass;
            }
        }
    }
}