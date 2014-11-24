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
        $this->addCommonAssets($assetManagerDef, $container->getParameter('fxp_require_asset.assetic.config.common_assets'));

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
     * @param Definition $assetManagerDef The asset manager
     * @param array      $commonAssets    The config of common assets
     */
    protected function addCommonAssets(Definition $assetManagerDef, array $commonAssets)
    {
        foreach ($commonAssets as $formulaeName => $commonAsset) {
            $commonAssetDef = $this->createCommonAssetDefinition($formulaeName, $commonAsset);
            $assetManagerDef->addMethodCall('addResource', array($commonAssetDef, 'fxp_require_asset_loader'));
        }
    }

    /**
     * Creates the commin asset definition.
     *
     * @param string $formulaeName   The formulae name of common asset
     * @param array  $formulaeConfig The formulae config of common asset (inputs, output, filters, options)
     *
     * @return Definition
     */
    protected function createCommonAssetDefinition($formulaeName, array $formulaeConfig)
    {
        $definition = new Definition();
        $definition
            ->setClass('Fxp\Component\RequireAsset\Assetic\Factory\Resource\CommonRequireAssetResource')
            ->setPublic(true)
            ->addArgument($formulaeName)
            ->addArgument($formulaeConfig['inputs'])
            ->addArgument($this->outputManager->convertOutput(trim($formulaeConfig['output'], '/')))
            ->addArgument($formulaeConfig['filters'])
            ->addArgument($formulaeConfig['options'])
        ;

        return $definition;
    }
}
