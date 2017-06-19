<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler;

use Fxp\Bundle\RequireAssetBundle\Assetic\Cache\ConfigAssetResourceCache;
use Fxp\Component\RequireAsset\Asset\Config\AssetReplacementManagerInterface;
use Fxp\Component\RequireAsset\Asset\Config\LocaleManagerInterface;
use Fxp\Component\RequireAsset\Assetic\AsseticAssetManager;
use Fxp\Component\RequireAsset\Assetic\AsseticAssetManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\AssetResourceInterface;
use Fxp\Component\RequireAsset\Assetic\Config\FileExtensionManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\OutputManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\PackageManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\PatternManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Util\PackageUtils;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Compile all assets config in cache.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CompilerAssetsPass implements CompilerPassInterface
{
    /**
     * @var OutputManagerInterface
     */
    protected $outputManager;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->getAssetReplacementManager($container)->addReplacements($container->getParameter('fxp_require_asset.config.asset_replacement'));
        $this->addConfigLocaleAssets($this->getLocaleManager($container), $container->getParameter('fxp_require_asset.config.locales'));
        $this->processForAssetic($container);
        $this->processLocales($container);
        $this->doProcessParameters($container);
    }

    /**
     * Process for the assetic.
     *
     * @param ContainerBuilder $container The container service
     */
    protected function processForAssetic(ContainerBuilder $container)
    {
        if (!$container->getParameter('fxp_require_asset.assetic')) {
            return;
        }

        $aam = $this->getAsseticAssetManager($container);
        $assetManagerDef = $container->getDefinition('assetic.asset_manager');
        $this->addConfigCommonAssets($aam, $container->getParameter('fxp_require_asset.assetic.config.common_assets'));

        $resources = $this->getAssetResources($container, $aam);

        foreach ($resources as $resource) {
            $assetDef = $this->createAssetDefinition($resource);
            $assetManagerDef->addMethodCall('addResource', array($assetDef, $resource->getLoader()));
        }

        $this->doProcessAsseticParameters($container, $aam->getPackageManager());
    }

    /**
     * Process the locales in the service definition of locale manager.
     *
     * @param ContainerBuilder $container
     */
    protected function processLocales(ContainerBuilder $container)
    {
        $localeManagerDef = $container->getDefinition('fxp_require_asset.config.locale_manager');
        $this->addLocaleAssets($localeManagerDef, $this->getLocaleManager($container)->getLocalizedAssets());
    }

    /**
     * Get the config asset resources.
     *
     * @param ContainerBuilder             $container The container service
     * @param AsseticAssetManagerInterface $aam       The assetic asset manager
     *
     * @return AssetResourceInterface[]
     */
    protected function getAssetResources(ContainerBuilder $container, AsseticAssetManagerInterface $aam)
    {
        $debug = (bool) $container->getParameter('assetic.debug');
        $cache = $this->getConfigAssetCache($container);

        if ($cache->hasResources()) {
            $resources = $cache->getResources();
            $currentCache = new ConfigAssetResourceCache($container->getParameter('assetic.cache_dir'));
            $currentCache->setResources($resources);
        } else {
            $configs = $aam->getAsseticConfigResources($debug);
            $resources = $configs->getResources();
            $cache->setResources($resources);
        }

        return $resources;
    }

    /**
     * Get the first config asset resource cache.
     *
     * @param ContainerBuilder $container The container service
     *
     * @return ConfigAssetResourceCache
     */
    protected function getConfigAssetCache(ContainerBuilder $container)
    {
        $dir = str_replace('\\', '/', $container->getParameter('assetic.cache_dir'));
        $kernelName = $container->getParameter('kernel.name');
        $pos = strrpos($kernelName, '_');

        if (false !== $pos && '_' === substr($kernelName, $pos)) {
            $env = $container->getParameter('kernel.environment');
            $tmpEnv = substr($env, 0, strlen($env) - 1).'_';
            $dir = str_replace('/'.$tmpEnv.'/', '/'.$env.'/', $dir);
        }

        return new ConfigAssetResourceCache($dir);
    }

    /**
     * Add the common asset config in require asset manager.
     *
     * @param AsseticAssetManagerInterface $aam          The require asset manager
     * @param array                        $commonAssets The common asset configs
     */
    protected function addConfigCommonAssets(AsseticAssetManagerInterface $aam, array $commonAssets)
    {
        foreach ($commonAssets as $commonName => $commonConfig) {
            $aam->addCommonAsset($commonName,
                $commonConfig['inputs'],
                $commonConfig['output'],
                $commonConfig['filters'],
                $commonConfig['options']
            );
        }
    }

    /**
     * Add the localized assets in locale manager.
     *
     * @param LocaleManagerInterface $lm The locale manager
     * @param array
     */
    protected function addConfigLocaleAssets(LocaleManagerInterface $lm, array $localConfigs)
    {
        /* @var array $assetConfigs */
        foreach ($localConfigs as $locale => $assetConfigs) {
            /* @var array $localizedAssets */
            foreach ($assetConfigs as $assetSource => $localizedAssets) {
                $lm->addLocalizedAsset($assetSource, $locale, $localizedAssets);
            }
        }
    }

    /**
     * Adds the locale assets.
     *
     * @param Definition $localeManagerDef The locale manager
     * @param array      $localeAssets     The config of locale assets
     */
    protected function addLocaleAssets(Definition $localeManagerDef, array $localeAssets)
    {
        /* @var array $assetConfigs */
        foreach ($localeAssets as $locale => $assetConfigs) {
            /* @var array $localizedAssets */
            foreach ($assetConfigs as $assetSource => $localizedAssets) {
                $localeManagerDef->addMethodCall('addLocalizedAsset', array($assetSource, $locale, $localizedAssets));
            }
        }
    }

    /**
     * Creates the asset definition.
     *
     * @param AssetResourceInterface $resource The config asset resource
     *
     * @return Definition
     */
    protected function createAssetDefinition(AssetResourceInterface $resource)
    {
        $definition = new Definition($resource->getClassname(), $resource->getArguments());
        $definition->setPublic(true);

        return $definition;
    }

    /**
     * Get the require asset manager.
     *
     * @param ContainerBuilder $container The container builder
     *
     * @return AsseticAssetManager The require asset manager
     */
    protected function getAsseticAssetManager(ContainerBuilder $container)
    {
        /* @var FileExtensionManagerInterface $extManager */
        $extManager = $container->get('fxp_require_asset.assetic.config.file_extension_manager');
        /* @var PatternManagerInterface $patternManager */
        $patternManager = $container->get('fxp_require_asset.assetic.config.pattern_manager');
        /* @var OutputManagerInterface $outputManager */
        $outputManager = $container->get('fxp_require_asset.assetic.config.output_manager');
        /* @var PackageManagerInterface $packageManager */
        $packageManager = $container->get('fxp_require_asset.assetic.config.package_manager');

        $aam = new AsseticAssetManager();
        $aam->setFileExtensionManager($extManager)
            ->setPatternManager($patternManager)
            ->setOutputManager($outputManager)
            ->setLocaleManager($this->getLocaleManager($container))
            ->setAssetReplacementManager($this->getAssetReplacementManager($container))
            ->setPackageManager($packageManager);

        return $aam;
    }

    /**
     * Process container parameters.
     *
     * @param ContainerBuilder $container The container service
     */
    protected function doProcessParameters(ContainerBuilder $container)
    {
        /* @var ParameterBag $pb */
        $pb = $container->getParameterBag();
        $pb->remove('fxp_require_asset.assetic.config.common_assets');
        $pb->remove('fxp_require_asset.config.asset_replacement');
    }

    /**
     * Process container parameters.
     *
     * @param ContainerBuilder        $container The container service
     * @param PackageManagerInterface $manager   The package manager
     */
    protected function doProcessAsseticParameters(ContainerBuilder $container, PackageManagerInterface $manager)
    {
        /* @var ParameterBag $pb */
        $pb = $container->getParameterBag();
        $pb->remove('fxp_require_asset.assetic.config.common_assets');
        $pb->remove('fxp_require_asset.assetic');
        $pb->set('fxp_require_asset.package_dirs', PackageUtils::getPackagePaths($manager));
    }

    /**
     * Get the locale manager.
     *
     * @param ContainerBuilder $container The container builder
     *
     * @return LocaleManagerInterface
     */
    protected function getLocaleManager(ContainerBuilder $container)
    {
        return $container->get('fxp_require_asset.config.locale_manager');
    }

    /**
     * Get the asset replacement manager.
     *
     * @param ContainerBuilder $container The container builder
     *
     * @return AssetReplacementManagerInterface
     */
    protected function getAssetReplacementManager(ContainerBuilder $container)
    {
        return $container->get('fxp_require_asset.config.asset_replacement_manager');
    }
}
