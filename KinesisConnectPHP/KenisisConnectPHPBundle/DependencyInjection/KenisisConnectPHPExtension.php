<?php

namespace KenisisConnectPHP\KenisisConnectPHPBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KenisisConnectPHPExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('kenisis_connect.kinesis_stream_name', $config['kinesis_stream_name']);
        $container->setParameter('kenisis_connect.api_dragibus_url_profile', $config['api_dragibus_url_profile']);
        $container->setParameter('kenisis_connect.api_dragibus_url_matching', $config['api_dragibus_url_matching']);
        $container->setParameter('kenisis_connect.api_get_segments', $config['api_get_segments']);
        if (isset($config['aws'])) {
            if (isset($config['aws']['key'])) {
                $container->setParameter('kenisis_connect.aws.key', $config['aws']['key']);
            }
            if (isset($config['aws']['secret'])) {
                $container->setParameter('kenisis_connect.aws.secret', $config['aws']['secret']);
            }
            if (isset($config['aws']['region'])) {
                $container->setParameter('kenisis_connect.aws.region', $config['aws']['region']);
            }
        }
    }
}
