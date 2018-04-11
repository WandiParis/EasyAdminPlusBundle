<?php

namespace Wandi\EasyAdminPlusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class WandiEasyAdminPlusExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $config = $this->processConfigTranslator($config, $container);

        $container->setParameter('easy_admin_plus', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    private function processConfigTranslator(array $config, ContainerBuilder $container): array
    {
        if (empty($config['translator']['paths'])) {
            $config['translator']['paths'] = [
                $container->getParameter('kernel.project_dir').'/translations',
            ];
        }
        if (empty($config['translator']['locales'])) {
            $config['translator']['locales'] = [
                $container->getParameter('locale') ?? $container->getParameter('kernel.default_locale'),
            ];
        }

        return $config;
    }
}
