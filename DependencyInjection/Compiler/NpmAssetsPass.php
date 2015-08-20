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
 * Adds all native npm packages installed by NPM.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class NpmAssetsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('fxp_require_asset.native_npm')) {
            return;
        }

        $packageManagerDef = $container->getDefinition('fxp_require_asset.assetic.config.package_manager');
        $baseDir = $container->getParameter('fxp_require_asset.base_dir');
        $dir = rtrim(str_replace('\\', '/', $baseDir.'/node_modules'), '/');
        $packages = AssetUtils::findPackages('npm', 'package.json', $dir, 'name');

        AssetUtils::addPackages($packageManagerDef, $packages);
    }
}
