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

use Fxp\Component\RequireAsset\Assetic\Config\OutputManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\PackageInterface;
use Fxp\Component\RequireAsset\Assetic\RequireLocaleManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Util\LocaleUtils;
use Fxp\Component\RequireAsset\Assetic\Util\ResourceUtils;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Finder\SplFileInfo;

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
        $idManager = 'fxp_require_asset.assetic.config.package_manager';
        $idOutputManager = 'fxp_require_asset.assetic.config.output_manager';
        $manager = $container->get($idManager);
        $this->outputManager = $container->get($idOutputManager);
        $localeManagerDef = $container->getDefinition('fxp_require_asset.assetic.locale_manager');
        $assetManagerDef = $container->getDefinition('assetic.asset_manager');
        $this->debug = (bool) $container->getParameter('assetic.debug');

        foreach ($manager->getPackages() as $package) {
            $this->addPackageAssets($assetManagerDef, $package);
        }
        $this->addLocaleAssets($localeManagerDef, $container->getParameter('fxp_require_asset.assetic.config.locales'));
        $this->addCommonAssets($assetManagerDef, $container->getParameter('fxp_require_asset.assetic.config.common_assets'), $localeManagerDef, $container->get('fxp_require_asset.assetic.locale_manager'));

        /* @var ParameterBag $pb */
        $pb = $container->getParameterBag();
        $pb->remove('fxp_require_asset.assetic.config.locales');
        $pb->remove('fxp_require_asset.assetic.config.common_assets');
    }

    /**
     * Adds the assets of packages.
     *
     * @param Definition       $assetManagerDef The asset manager
     * @param PackageInterface $package         The asset package instance
     */
    protected function addPackageAssets(Definition $assetManagerDef, PackageInterface $package)
    {
        foreach ($package->getFiles($this->debug) as $file) {
            $assetDef = $this->createAssetDefinition($package, $file);
            $assetManagerDef->addMethodCall('addResource', array($assetDef, 'fxp_require_asset_loader'));
        }
    }

    /**
     * Creates the asset definition.
     *
     * @param PackageInterface $package The asset package instance
     * @param SplFileInfo      $file    The Spo file info instance
     *
     * @return Definition
     */
    protected function createAssetDefinition(PackageInterface $package, SplFileInfo $file)
    {
        $c = ResourceUtils::createConfigResource($package, $file, $this->outputManager);
        $definition = new Definition();
        $definition
            ->setClass('Fxp\Component\RequireAsset\Assetic\Factory\Resource\RequireAssetResource')
            ->setPublic(true)
            ->addArgument($c[0])
            ->addArgument($c[1])
            ->addArgument($c[2])
            ->addArgument($c[3])
            ->addArgument($c[4])
        ;

        return $definition;
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
                $localeManagerDef->addMethodCall('addLocaliszedAsset', array($assetSource, $locale, $localizedAssets));
            }
        }
    }

    /**
     * Adds the common assets.
     *
     * @param Definition                    $assetManagerDef  The asset manager
     * @param array                         $commonAssets     The config of common assets
     * @param Definition                    $localeManagerDef The locale manager definition
     * @param RequireLocaleManagerInterface $localeManager    The locale manager
     */
    protected function addCommonAssets(Definition $assetManagerDef, array $commonAssets, Definition $localeManagerDef, RequireLocaleManagerInterface $localeManager)
    {
        foreach ($commonAssets as $commonName => $commonConfig) {
            $commonAssetDef = $this->createCommonAssetDefinition($commonName, $commonConfig);
            $assetManagerDef->addMethodCall('addResource', array($commonAssetDef, 'fxp_require_asset_loader'));

            if (!preg_match('/__[A-Za-z]{2}$|__[A-Za-z]{2}_[A-Za-z]{2}$/', $commonName)) {
                $this->addLocaleCommonAssets($assetManagerDef, $localeManagerDef, $commonName, $commonConfig, $localeManager, $commonAssets);
            } else {
                $name = substr($commonName, 0, strrpos($commonName, '__'));
                $locale = substr($commonName, strrpos($commonName, '__') + 2);
                $localeManagerDef->addMethodCall('addLocaliszedAsset', array($name, $locale, array($commonName)));
            }
        }
    }

    /**
     * Adds the locale common assets of common asset.
     *
     * @param Definition                    $assetManagerDef  The asset manager
     * @param Definition                    $localeManagerDef The locale manager definition
     * @param string                        $commonName       The formulae name of common asset
     * @param array                         $commonConfig     The formulae config of common asset (inputs, output, filters, options)
     * @param RequireLocaleManagerInterface $localeManager    The locale manager
     * @param array                         $commonAssets     The common asset configs
     */
    protected function addLocaleCommonAssets(Definition $assetManagerDef, Definition $localeManagerDef, $commonName, array $commonConfig, RequireLocaleManagerInterface $localeManager, array $commonAssets)
    {
        $locales = LocaleUtils::findCommonAssetLocales($commonConfig['inputs'], $localeManager);

        foreach ($locales as $locale) {
            $localeName = LocaleUtils::formatLocaleCommonName($commonName, $locale);
            if (!isset($commonAssets[$localeName])) {
                $commonAssetDef = $this->createLocaleCommonAssetDefinition(
                    $commonName,
                    $commonConfig,
                    $locale,
                    $localeManager
                );
                $assetManagerDef->addMethodCall('addResource', array($commonAssetDef, 'fxp_require_asset_loader'));
                $localeManagerDef->addMethodCall('addLocaliszedAsset', array($commonName, $locale, array($localeName)));
            }
        }
    }

    /**
     * Creates the common asset definition.
     *
     * @param string $commonName   The formulae name of common asset
     * @param array  $commonConfig The formulae config of common asset (inputs, output, filters, options)
     *
     * @return Definition
     */
    protected function createCommonAssetDefinition($commonName, array $commonConfig)
    {
        $definition = new Definition();
        $definition
            ->setClass('Fxp\Component\RequireAsset\Assetic\Factory\Resource\CommonRequireAssetResource')
            ->setPublic(true)
            ->addArgument($commonName)
            ->addArgument($commonConfig['inputs'])
            ->addArgument($this->outputManager->convertOutput(trim($commonConfig['output'], '/')))
            ->addArgument($commonConfig['filters'])
            ->addArgument($commonConfig['options'])
        ;

        return $definition;
    }

    /**
     * Creates the locale common asset definition with the common asset config.
     *
     * @param string                        $commonName    The formulae name of common asset
     * @param array                         $commonConfig  The formulae config of common asset (inputs, output, filters, options)
     * @param string                        $locale        The locale
     * @param RequireLocaleManagerInterface $localeManager The locale manager
     *
     * @return Definition
     */
    protected function createLocaleCommonAssetDefinition($commonName, array $commonConfig, $locale, RequireLocaleManagerInterface $localeManager)
    {
        $commonName = LocaleUtils::formatLocaleCommonName($commonName, $locale);
        $commonConfig['inputs'] = LocaleUtils::getLocaleCommonInputs($commonConfig['inputs'], $locale, $localeManager);
        $commonConfig['output'] = LocaleUtils::convertLocaleTartgetPath($commonConfig['output'], $locale);

        return $this->createCommonAssetDefinition($commonName, $commonConfig);
    }
}
