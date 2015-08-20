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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds all native bower packages installed by Bower.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BowerAssetsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('fxp_require_asset.native_bower')) {
            return;
        }

        $packageManagerDef = $container->getDefinition('fxp_require_asset.assetic.config.package_manager');
        $dir = $this->getBowerDirectory($container);
        $packages = AssetUtils::findPackages('bower', '.bower.json', $dir, 'name');

        AssetUtils::addPackages($packageManagerDef, $packages);
    }

    /**
     * Get the directory installation of bower.
     *
     * @param ContainerBuilder $container The container service
     *
     * @return string
     */
    protected function getBowerDirectory(ContainerBuilder $container)
    {
        $baseDir = $container->getParameter('fxp_require_asset.base_dir');
        $bowerrc = $baseDir.'/.bowerrc';
        $directory = 'bower_components';

        if (file_exists($bowerrc)) {
            $config = AssetUtils::loadJsonFile($bowerrc);
            $directory = isset($config['directory']) ? $config['directory'] : $directory;
        }

        return rtrim(str_replace('\\', '/', $baseDir.'/'.$directory), '/');
    }
}
