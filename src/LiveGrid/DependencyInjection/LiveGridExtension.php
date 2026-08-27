<?php

namespace App\LiveGrid\DependencyInjection;

use App\LiveGrid\Port\FilterHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class LiveGridExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /** @var array{grids: array<string, array{adapter: string, filter_form?: string|null, template?: string|null, options?: array<string, mixed>, hooks: array<string, array<string, array{template: string, priority?: int}>>}>, hooks: array<string, array<string, array{template: string, priority?: int}>>} $config */
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('live_grid.grids', $config['grids']);
        $container->setParameter('live_grid.hooks', $config['hooks']);

        /** @var array<string, array<string, array{template: string, priority?: int}>> $mergedHooks */
        $mergedHooks = $config['hooks'];
        foreach ($config['grids'] as $gridName => $gridConfig) {
            foreach ($gridConfig['hooks'] as $hookSuffix => $entries) {
                $hookName = str_starts_with($hookSuffix, 'live_grid.')
                    ? $hookSuffix
                    : sprintf('live_grid.%s.%s', $gridName, $hookSuffix);
                $mergedHooks[$hookName] = array_replace($mergedHooks[$hookName] ?? [], $entries);
            }
        }
        $container->setParameter('live_grid.merged_hooks', $mergedHooks);

        $container->registerForAutoconfiguration(FilterHandlerInterface::class)
            ->addTag('live_grid.filter_handler');

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('twig', [
            'paths' => [
                \dirname(__DIR__) . '/Resources/views' => 'LiveGrid',
            ],
        ]);
    }

    public function getAlias(): string
    {
        return 'live_grid';
    }
}
