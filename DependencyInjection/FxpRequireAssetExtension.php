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
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container->getParameter('kernel.root_dir'));
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('twig.xml');
        $loader->load('assetic.xml');

        $this->configureAssetic($container, $config['output_prefix'], $config['output_prefix_debug'], $config['composer_installed_path'], $config['base_dir']);
        $this->configureFileExtensionManager($container, $config['default']);

        $default = $config['default'];
        $container->setParameter('fxp_require_asset.assetic.config.file_extensions', $default['extensions']);
        $container->setParameter('fxp_require_asset.assetic.config.patterns', $default['patterns']);
        $container->setParameter('fxp_require_asset.assetic.config.packages', $config['packages']);
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
    protected function configureAssetic(ContainerBuilder $container, $output,$outputDebug, $composerInstalled, $baseDir)
    {
        $debug = $container->getParameter('assetic.debug');
        $output = $debug ? $outputDebug : $output;

        $container->setParameter('fxp_require_asset.output_prefix', $output);
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
            $def->addMethodCall('addDefaultExtensions', array($this->getDefaultExtensions()));
        }
    }

    /**
     * Gets the default file extensions.
     *
     * @return array
     */
    private function getDefaultExtensions()
    {
        return array(
            'map'  => array('debug' => true),
            'js'   => array(),
            'css'  => array(),
            'eot'  => array(),
            'svg'  => array(),
            'ttf'  => array(),
            'woff' => array(),
            'jpg'  => array(),
            'jpeg' => array(),
            'png'  => array(),
            'webp' => array(),
            'mp3'  => array(),
            'aac'  => array(),
            'wav'  => array(),
            'ogg'  => array(),
            'webm' => array(),
            'mp4'  => array(),
            'ogv'  => array(),
        );
    }
}
