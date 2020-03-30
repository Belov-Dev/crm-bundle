<?php

namespace A2Global\CRMBundle\Modifier;

use A2Global\CRMBundle\Builder\ProxyEntityBuilder;
use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Utility\StringUtility;

class ProxyEntityModifier
{
    private $proxyEntityBuilder;

    public function __construct(ProxyEntityBuilder $proxyEntityBuilder)
    {
        $this->proxyEntityBuilder = $proxyEntityBuilder;
    }

    public function update(Entity $entity)
    {
        $cacheDirectory = __DIR__ . '/../../var/cache';

        if (!is_dir($cacheDirectory)) {
            throw new \Exception('Cache directory not found: ' . $cacheDirectory);
        }
        $directory = $cacheDirectory . '/a2crm';

        if (!is_dir($directory)) {
            if(false === @mkdir($directory, 0775, true)){
                throw new \Exception('Failed to create cache subdirectory: ' . $directory);
            }
        }

        if (!is_writable($directory)) {
            throw new \Exception('Cache subdirectory is not writeable: ' . $directory);
        }
        $filepath = $directory.'/'.StringUtility::toPascalCase($entity->getName()).'.php';
        file_put_contents($filepath, $this->proxyEntityBuilder->buildForEntity($entity));
        @chmod($filepath, 0664);
    }
}