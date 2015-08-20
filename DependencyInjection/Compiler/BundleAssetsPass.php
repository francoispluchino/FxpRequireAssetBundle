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

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds all assets in bundle located in 'Resources/assets'.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BundleAssetsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $packageManagerDef = $container->getDefinition('fxp_require_asset.assetic.config.package_manager');
        $bundles = $container->getParameter('kernel.bundles');

        foreach ($bundles as $name => $class) {
            $ref = new \ReflectionClass($class);
            $path = realpath(dirname($ref->getFileName()).'/Resources');

            if ($path) {
                $package = $this->createPackageConfig($name, $path);
                $packageManagerDef->addMethodCall('addPackage', array($package));
            }
        }
    }

    /**
     * Create the package config of bundle.
     *
     * @param string $name The bundle name
     * @param string $path The real path of bundle
     *
     * @return array
     */
    protected function createPackageConfig($name, $path)
    {
        $id = Container::underscore($name);
        $sourceBase = substr($id, 0, strrpos($id, '_bundle'));
        $sourceBase = str_replace('_', '', $sourceBase);

        return array(
            'name' => $id,
            'source_path' => $path,
            'source_base' => $sourceBase,
            'patterns' => array(
                '!config/*',
                '!doc/*',
                '!license/*',
                '!licenses/*',
                '!meta/*',
                '!public/*',
                '!skeleton/*',
            ),
        );
    }
}
