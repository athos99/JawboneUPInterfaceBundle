<?php

namespace AG\JawboneUPInterfaceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class AGJawboneUPInterfaceExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('ag_jawbone_up_interface.client_id', $config['client_id']);
        $container->setParameter('ag_jawbone_up_interface.client_secret', $config['client_secret']);
        $container->setParameter('ag_jawbone_up_interface.callback', $config['callback']);
        $container->setParameter('ag_jawbone_up_interface.scopes', $config['scopes']);
        $container->setParameter('ag_jawbone_up_interface.configuration', array());
    }

    public function getAlias()
    {
        return 'ag_jawbone_up_interface';
    }
}
