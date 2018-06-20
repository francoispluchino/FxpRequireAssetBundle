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

use Fxp\Component\RequireAsset\Config\AssetReplacementConfiguration;
use Fxp\Component\RequireAsset\Config\LocaleConfiguration;
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
    protected $projectDir;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * Constructor.
     *
     * @param string $projectDir
     * @param string $defaultLocale
     */
    public function __construct($projectDir, $defaultLocale)
    {
        $this->projectDir = $projectDir;
        $this->defaultLocale = $defaultLocale;
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
                ->booleanNode('twig')->defaultValue(class_exists('Twig_Environment'))->end()
                ->scalarNode('default_locale')->defaultValue($this->defaultLocale)->end()
                ->scalarNode('fallback_locale')->defaultNull()->end()
                ->append(AssetReplacementConfiguration::getNodeDefinition())
                ->append(LocaleConfiguration::getNodeDefinition())
                ->append($this->getWebpackNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Get webpack config node.
     *
     * @return NodeDefinition
     */
    private function getWebpackNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('webpack');

        $node
            ->canBeDisabled()
            ->addDefaultsIfNotSet()
            ->children()
                ->enumNode('adapter')
                    ->values(['auto', 'manifest', 'assets'])
                    ->defaultValue('auto')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('manifest_adapter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('file')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('assets_adapter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('file')->defaultValue($this->projectDir.'/assets.json')->end()
                        ->arrayNode('cache')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultValue(null)->end()
                                ->booleanNode('key')->defaultValue('fxp_require_asset_webpack_assets')->end()
                                ->scalarNode('service_id')->defaultValue('cache.app')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
