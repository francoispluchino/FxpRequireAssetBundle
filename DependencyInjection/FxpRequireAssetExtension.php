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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration($container->getParameter('kernel.project_dir'), 'en');
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('asset.xml');

        if ($config['twig']) {
            $loader->load('twig.xml');
            $loader->load('exception_listener.xml');
        }

        $this->loadParameters($container, $config);
        $this->configureWebpack($loader, $container, $config['webpack'], $config['twig']);
    }

    /**
     * Load the parameters in container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadParameters(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('fxp_require_asset.config.default_locale', $config['default_locale']);
        $container->setParameter('fxp_require_asset.config.fallback_locale', $config['fallback_locale']);
        $container->setParameter('fxp_require_asset.config.locales', $config['locales']);
    }

    /**
     * Configure the webpack section.
     *
     * @param LoaderInterface  $loader    The service loader
     * @param ContainerBuilder $container The container
     * @param array            $config    The webpack config
     * @param bool             $withTwig  Check if the twig feature must be used
     */
    protected function configureWebpack(LoaderInterface $loader, ContainerBuilder $container, array $config, $withTwig): void
    {
        if ($config['enabled']) {
            $loader->load('webpack.xml');

            $container->setParameter('fxp_require_asset.webpack.adapter', $config['adapter']);
            $this->configureWebpackManifest($container, $config['manifest_adapter']);
            $this->configureWebpackAssets($container, $config['assets_adapter']);

            if ($withTwig) {
                $loader->load('twig_webpack.xml');
            }
        }
    }

    /**
     * Configure the webpack manifest adapter.
     *
     * @param ContainerBuilder $container The container
     * @param array            $config    The webpack config
     */
    private function configureWebpackManifest(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('fxp_require_asset.webpack.adapter.manifest.file', $config['file']);
    }

    /**
     * Configure the webpack assets adapter.
     *
     * @param ContainerBuilder $container The container
     * @param array            $config    The webpack config
     */
    private function configureWebpackAssets(ContainerBuilder $container, array $config): void
    {
        $assetsFile = $config['file'];
        $cacheId = $config['cache']['service_id'];
        $cacheKey = $config['cache']['key'];
        $cacheEnabled = $config['cache']['enabled'];
        $cacheEnabled = null !== $cacheEnabled
            ? $cacheEnabled
            : !$container->getParameter('kernel.debug');

        $container->setParameter('fxp_require_asset.webpack.adapter.assets.file', $assetsFile);
        $container->setParameter('fxp_require_asset.webpack.adapter.assets.cache_key', $cacheKey);

        if ($cacheEnabled) {
            $container->setAlias('fxp_require_asset.webpack.adapter.assets.cache', $cacheId);
        }
    }
}
