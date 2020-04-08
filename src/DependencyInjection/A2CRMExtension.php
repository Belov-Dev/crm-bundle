<?php

namespace A2Global\CRMBundle\DependencyInjection;

use A2Global\CRMBundle\Component\Field\FieldInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class A2CRMExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $configs = $this->processConfiguration($configuration, $configs);

         $container
            ->registerForAutoconfiguration(FieldInterface::class)
            ->addTag('a2crm.entity_field');
    }

    public function getAlias()
    {
        return 'a2crm';
    }
}