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

use Fxp\Component\RequireAsset\Assetic\Util\FileExtensionUtils;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpRequireAssetExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container->getParameter('kernel.root_dir'), $container->getParameter('locale'));
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('twig.xml');
        $loader->load('assetic.xml');
        $loader->load('assetic_filter.xml');

        $this->configureAssetic($container, $config['output_prefix'], $config['output_prefix_debug'], $config['composer_installed_path'], $config['base_dir']);
        $this->configureFileExtensionManager($container, $config['default']);
        $this->loadParameters($container, $config);
    }

    /**
     * Load the parameters in container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadParameters(ContainerBuilder $container, array $config)
    {
        $default = $config['default'];
        $container->setParameter('fxp_require_asset.assetic.config.file_extensions', $default['extensions']);
        $container->setParameter('fxp_require_asset.assetic.config.patterns', $default['patterns']);
        $container->setParameter('fxp_require_asset.assetic.config.output_rewrites', $config['output_rewrites']);
        $container->setParameter('fxp_require_asset.assetic.config.packages', $config['packages']);
        $container->setParameter('fxp_require_asset.assetic.config.asset_replacement', $config['asset_replacement']);
        $container->setParameter('fxp_require_asset.assetic.config.common_assets', $config['common_assets']);
        $container->setParameter('fxp_require_asset.assetic.config.default_locale', $config['default_locale']);
        $container->setParameter('fxp_require_asset.assetic.config.fallback_locale', $config['fallback_locale']);
        $container->setParameter('fxp_require_asset.assetic.config.locales', $config['locales']);
    }

    /**
     * Configure assetic.
     *
     * @param ContainerBuilder $container
     * @param string           $output
     * @param string           $outputDebug
     * @param string           $composerInstalled
     * @param string           $baseDir
     */
    protected function configureAssetic(ContainerBuilder $container, $output, $outputDebug, $composerInstalled, $baseDir)
    {
        $debug = $container->getParameter('assetic.debug');
        $output = $debug ? $outputDebug : $output;

        $container->setParameter('fxp_require_asset.output_prefix', (string) $output);
        $container->setParameter('fxp_require_asset.composer_installed_path', $composerInstalled);
        $container->setParameter('fxp_require_asset.base_dir', $baseDir);
    }

    /**
     * Configure the default file extentions section.
     *
     * @param ContainerBuilder $container
     * @param array            $default
     */
    protected function configureFileExtensionManager(ContainerBuilder $container, array $default)
    {
        if (!$default['replace_extensions']) {
            $def = $container->getDefinition('fxp_require_asset.assetic.config.file_extension_manager');
            $def->addMethodCall('addDefaultExtensions', array(FileExtensionUtils::getDefaultConfigs()));
        }
    }
}
