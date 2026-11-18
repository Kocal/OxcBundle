<?php

declare(strict_types=1);

namespace Kocal\OxcBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

final class OxcBundle extends Extension implements ConfigurationInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.php');

        $configuration = $this->getConfiguration($configs, $container);
        /** @var array{oxlint_version: ?string, oxfmt_version: ?string, apps_version: ?string} $config */
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition('oxc.command.download_oxlint')
            ->setArgument(2, $config['oxlint_version'])
            ->setArgument(3, $config['apps_version']);

        $container->getDefinition('oxc.command.download_oxfmt')
            ->setArgument(2, $config['oxfmt_version'])
            ->setArgument(3, $config['apps_version']);
    }

    /**
     * @param array<mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return $this;
    }

    public function getAlias(): string
    {
        return 'kocal_oxc';
    }

    /**
     * @return TreeBuilder<'array'>
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($this->getAlias());
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('oxlint_version')
                    ->info('The Oxlint version to download, from git tags "oxlint_v*" (or "apps_v*" up to 1.84.0).')
                    ->defaultNull()
                    ->example('1.94.0')
                ->end()
                ->scalarNode('oxfmt_version')
                    ->info('The Oxfmt version to download, from git tags "oxfmt_v*" (0.70.0 and newer).')
                    ->defaultNull()
                    ->example('0.79.0')
                ->end()
                ->scalarNode('apps_version')
                    ->info('The version of git tag "apps_v*" to download, for both Oxlint and Oxfmt. For example, git tag "apps_v1.84.0" corresponds to version "1.84.0".')
                    ->setDeprecated('kocal/oxc-bundle', '1.1', 'The "%node%" option is deprecated, use "oxlint_version" and "oxfmt_version" instead.')
                    ->defaultNull()
                    ->example('1.84.0')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
