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
        /** @var array<string> $config */
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition('oxc.command.download_oxlint')
            ->setArgument(2, $config['apps_version']);

        $container->getDefinition('oxc.command.download_oxfmt')
            ->setArgument(2, $config['apps_version']);
    }

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
                ->scalarNode('apps_version')
                    ->info('The version of git tag "apps_v*" to download. For example, git tag "apps_v1.43.0" corresponds to version "1.43.0".')
                    ->isRequired()
                    ->example('1.43.0')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
