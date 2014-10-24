<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\DependencyInjection;

use Fxp\Component\RequireAsset\Config\FileExtensionConfiguration;
use Fxp\Component\RequireAsset\Config\PackageConfiguration;
use Fxp\Component\RequireAsset\Config\PatternConfiguration;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * Constructor.
     *
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fxp_require_asset');

        $rootNode
            ->children()
                ->scalarNode('output_prefix')->defaultValue('assets')->end()
                ->scalarNode('output_prefix_debug')->defaultValue('assets-dev')->end()
                ->scalarNode('composer_installed_path')
                    ->defaultValue($this->rootDir . '/../vendor/composer/installed.json')
                    ->end()
                ->scalarNode('base_dir')->defaultValue($this->rootDir . '/..')->end()
            ->end()
            ->append($this->getDefaultForPackageNode())
            ->append($this->getOutputRewritesNode())
            ->append(PackageConfiguration::getNodeDefinition())
        ;

        return $treeBuilder;
    }

    /**
     * Get default config node for package.
     *
     * @return NodeDefinition
     */
    private function getDefaultForPackageNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('default');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('replace_extensions')->defaultFalse()->end()
                ->append(FileExtensionConfiguration::getNodeDefinition())
                ->append(PatternConfiguration::getNodeDefinition())
            ->end()
        ;

        return $node;
    }

    /**
     * Get packages config node.
     *
     * @return NodeDefinition
     */
    private function getOutputRewritesNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('output_rewrites');

        $node
            ->useAttributeAsKey('name')
            ->normalizeKeys(false)
            ->prototype('variable')->end()
        ;

        return $node;
    }
}
