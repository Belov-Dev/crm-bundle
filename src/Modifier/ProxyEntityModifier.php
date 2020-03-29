<?php

namespace A2Global\CRMBundle\Modifier;

use A2Global\CRMBundle\Builder\ProxyEntityBuiler;
use A2Global\CRMBundle\Entity\Entity;

class ProxyEntityModifier
{
    private $proxyEntityBuiler;

    public function __construct(ProxyEntityBuiler $proxyEntityBuiler)
    {
        $this->proxyEntityBuiler = $proxyEntityBuiler;
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
        $filepath = $directory.'/'.ucfirst($entity->getName()).'.php';
        file_put_contents($filepath, $this->proxyEntityBuiler->buildForEntity($entity));
        @chmod($filepath, 0664);
    }
}