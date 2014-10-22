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

use Fxp\Component\RequireAsset\Assetic\Config\FileExtensionInterface;
use Fxp\Component\RequireAsset\Assetic\Config\OutputManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Config\PackageInterface;
use Fxp\Component\RequireAsset\Assetic\Util\Utils;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Compile all assets config in cache.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CompilerAssetsPass implements CompilerPassInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

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
        $assetManagerDef = $container->getDefinition('assetic.asset_manager');
        $this->filesystem = new Filesystem();
        $this->debug = (bool) $container->getParameter('assetic.debug');

        foreach ($manager->getPackages() as $package) {
            $this->addPackageAssets($assetManagerDef, $package);
        }
    }

    /**
     * Gets the assets of packages.
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
        $name = $this->getAsseticName($package, $file);

        $output = $this->getPathRelative($package, $file);
        $filters = array();
        $options = array();
        $ext = $file->getExtension();
        $output = $package->getSourceBase() . '/' . $output;

        if ($package->hasExtension($ext)) {
            $pExt = $package->getExtension($ext);
            $filters = $pExt->getFilters();
            $options = $pExt->getOptions();
            $output = $this->replaceExtension($output, $pExt);
        }

        $definition = new Definition();
        $definition
            ->setClass('Fxp\Component\RequireAsset\Assetic\Factory\Resource\RequireAssetResource')
            ->setPublic(true)
            ->addArgument($name)
            ->addArgument($file->getLinkTarget())
            ->addArgument($this->outputManager->convertOutput($output))
            ->addArgument($filters)
            ->addArgument($options)
        ;

        return $definition;
    }

    /**
     * Get the path relative.
     *
     * @param PackageInterface $package The asset package instance
     * @param SplFileInfo      $file    The Spo file info instance
     *
     * @return string
     */
    protected function getPathRelative(PackageInterface $package, SplFileInfo $file)
    {
        $source = realpath($package->getSourcePath());

        return rtrim($this->filesystem->makePathRelative($file->getLinkTarget(), $source), '/');
    }

    /**
     * Get the assetic name of asset.
     *
     * @param PackageInterface $package The asset package instance
     * @param SplFileInfo      $file    The Spo file info instance
     *
     * @return string
     */
    protected function getAsseticName(PackageInterface $package, SplFileInfo $file)
    {
        $name = $package->getName() . '/' . $this->getPathRelative($package, $file);

        return Utils::formatName($name);
    }

    /**
     * Replace the file extension in output path.
     *
     * @param string                 $output The output path
     * @param FileExtensionInterface $ext    The config of file extension
     *
     * @return string
     */
    protected function replaceExtension($output, FileExtensionInterface $ext)
    {
        if (false !== $pos = strrpos($output, '.')) {
            $output = substr($output, 0, $pos) . '.' . $ext->getOutputExtension();
        }

        return $output;
    }
}
