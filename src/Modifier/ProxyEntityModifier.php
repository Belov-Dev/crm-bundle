<?php

namespace A2Global\CRMBundle\Modifier;

use A2Global\CRMBundle\Builder\ProxyEntityBuilder;
use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Provider\CacheDirectoryProvider;
use A2Global\CRMBundle\Utility\StringUtility;

class ProxyEntityModifier
{
    private $proxyEntityBuilder;

    private $cacheDirectoryProvider;

    public function __construct(ProxyEntityBuilder $proxyEntityBuilder, CacheDirectoryProvider $cacheDirectoryProvider)
    {
        $this->proxyEntityBuilder = $proxyEntityBuilder;
        $this->cacheDirectoryProvider = $cacheDirectoryProvider;
    }

    public function update(Entity $entity)
    {
        $cacheDirectory = $this->cacheDirectoryProvider->get();
        $filepath = $cacheDirectory . '/' . StringUtility::toPascalCase($entity->getName()) . '.php';
        file_put_contents($filepath, $this->proxyEntityBuilder->buildForEntity($entity));
        @chmod($filepath, 0664);
    }
}