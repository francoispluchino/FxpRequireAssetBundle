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
use Fxp\Component\RequireAsset\Assetic\Config\AssetReplacementManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\AssetResourceInterface;
use Fxp\Component\RequireAsset\Assetic\Config\FileExtensionManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\LocaleManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\OutputManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\PackageManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\PatternManagerInterface;
use Fxp\Component\RequireAsset\Assetic\RequireAssetManager;
use Fxp\Component\RequireAsset\Assetic\RequireAssetManagerInterface;
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
        $assetManagerDef = $container->getDefinition('assetic.asset_manager');
        $ram = $this->getRequireAssetManager($container);

        $ram->getAssetReplacementManager()->addReplacements($container->getParameter('fxp_require_asset.assetic.config.asset_replacement'));
        $this->addConfigCommonAssets($ram, $container->getParameter('fxp_require_asset.assetic.config.common_assets'));
        $this->addConfigLocaleAssets($ram, $container->getParameter('fxp_require_asset.assetic.config.locales'));

        $resources = $this->getAssetResources($container, $ram);

        foreach ($resources as $resource) {
            $assetDef = $this->createAssetDefinition($resource);
            $assetManagerDef->addMethodCall('addResource', array($assetDef, $resource->getLoader()));
        }

        $this->doProcessParameters($container, $ram->getPackageManager());
    }

    /**
     * Get the config asset resources.
     *
     * @param ContainerBuilder             $container The container service
     * @param RequireAssetManagerInterface $ram       The require asset manager
     *
     * @return AssetResourceInterface[]
     */
    protected function getAssetResources(ContainerBuilder $container, RequireAssetManagerInterface $ram)
    {
        $localeManagerDef = $container->getDefinition('fxp_require_asset.assetic.config.locale_manager');
        $debug = (bool) $container->getParameter('assetic.debug');
        $cache = $this->getConfigAssetCache($container);

        $this->addLocaleAssets($localeManagerDef, $ram->getLocaleManager()->getLocalizedAssets());

        if ($cache->hasResources()) {
            $resources = $cache->getResources();
            $currentCache = new ConfigAssetResourceCache($container->getParameter('assetic.cache_dir'));
            $currentCache->setResources($resources);
        } else {
            $configs = $ram->getAsseticConfigResources($debug);
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
     * @param RequireAssetManagerInterface $ram          The require asset manager
     * @param array                        $commonAssets The common asset configs
     */
    protected function addConfigCommonAssets(RequireAssetManagerInterface $ram, array $commonAssets)
    {
        foreach ($commonAssets as $commonName => $commonConfig) {
            $ram->addCommonAsset(
                $commonName,
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
     * @param RequireAssetManagerInterface $ram The require asset manager
     * @param array
     */
    protected function addConfigLocaleAssets(RequireAssetManagerInterface $ram, array $localConfigs)
    {
        /* @var array $assetConfigs */
        foreach ($localConfigs as $locale => $assetConfigs) {
            /* @var array $localizedAssets */
            foreach ($assetConfigs as $assetSource => $localizedAssets) {
                $ram->getLocaleManager()->addLocalizedAsset($assetSource, $locale, $localizedAssets);
            }
        }
    }

    /**
     * Adds the common assets.
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
     * @return RequireAssetManager The require asset manager
     */
    protected function getRequireAssetManager(ContainerBuilder $container)
    {
        $prefixId = 'fxp_require_asset.assetic.config.';
        /* @var FileExtensionManagerInterface $extManager */
        $extManager = $container->get($prefixId.'file_extension_manager');
        /* @var PatternManagerInterface $patternManager */
        $patternManager = $container->get($prefixId.'pattern_manager');
        /* @var OutputManagerInterface $outputManager */
        $outputManager = $container->get($prefixId.'output_manager');
        /* @var LocaleManagerInterface $localeManager */
        $localeManager = $container->get($prefixId.'locale_manager');
        /* @var PackageManagerInterface $packageManager */
        $packageManager = $container->get($prefixId.'package_manager');
        /* @var AssetReplacementManagerInterface $replacementManager */
        $replacementManager = $container->get($prefixId.'asset_replacement_manager');

        $ram = new RequireAssetManager();
        $ram->setFileExtensionManager($extManager)
            ->setPatternManager($patternManager)
            ->setOutputManager($outputManager)
            ->setLocaleManager($localeManager)
            ->setAssetReplacementManager($replacementManager)
            ->setPackageManager($packageManager);

        return $ram;
    }

    /**
     * Process container parameters.
     *
     * @param ContainerBuilder        $container The container service
     * @param PackageManagerInterface $manager   The package manager
     */
    protected function doProcessParameters(ContainerBuilder $container, PackageManagerInterface $manager)
    {
        /* @var ParameterBag $pb */
        $pb = $container->getParameterBag();
        $pb->remove('fxp_require_asset.assetic.config.locales');
        $pb->remove('fxp_require_asset.assetic.config.common_assets');
        $pb->remove('fxp_require_asset.assetic.config.asset_replacement');
        $pb->set('fxp_require_asset.package_dirs', PackageUtils::getPackagePaths($manager));
    }
}
