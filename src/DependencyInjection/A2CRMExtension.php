<?php

namespace A2Global\CRMBundle\DependencyInjection;

use A2Global\CRMBundle\DataGrid\DataGridInterface;
use A2Global\CRMBundle\DependencyInjection\CompilerPass\TwigCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Twig\Environment;

class A2CRMExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $configs = $this->processConfiguration($configuration, $configs);

//        $container->addCompilerPass(new TwigCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 50);
//
//        $twigEnvironment = $container->findDefinition('Twig\Environment');
//         $container
//            ->registerForAutoconfiguration(DataGridInterface::class)
//            ->addMethodCall('setTwigEnvironment', [$twigEnvironment]);

//        $container->registerForAutoconfiguration(DataGridInterface::class)->addTag('a2crm.datagrid');

//        $container
//            ->registerForAutoconfiguration(DataGridInterface::class)
//            ->addMethodCall('setTwigEnvironment', [$twigEnvironment]);
    }

    public function getAlias()
    {
        return 'a2crm';
    }
}