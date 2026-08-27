<?php

namespace App\Adapter\Sylius\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SyliusLiveGridExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('twig', [
            'paths' => [
                \dirname(__DIR__) . '/Resources/views' => 'SyliusLiveGrid',
            ],
        ]);

        // Default hooks for Sylius-backed grids are applied per-grid in app config;
        // shared partial templates live under @SyliusLiveGrid.
    }

    public function getAlias(): string
    {
        return 'sylius_live_grid';
    }
}
