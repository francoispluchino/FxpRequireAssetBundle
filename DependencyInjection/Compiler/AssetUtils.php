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

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Utils for compiler assets.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AssetUtils
{
    /**
     * Adds composer package.
     *
     * @param Definition $packageManagerDef
     * @param array      $packages
     */
    public static function addPackages(Definition $packageManagerDef, array $packages)
    {
        foreach ($packages as $name => $path) {
            $package = array(
                'name' => $name,
                'source_path' => $path,
                'source_base' => null,
            );
            $packageManagerDef->addMethodCall('addPackage', array($package));
        }
    }

    /**
     * Load the json file.
     *
     * @param string $path The full path
     *
     * @return array
     */
    public static function loadJsonFile($path)
    {
        if (file_exists($path)) {
            if (is_array($json = json_decode(file_get_contents($path), true))) {
                return $json;
            }
        }

        return array();
    }

    /**
     * Get the asset package name.
     *
     * @param string $type The asset type
     * @param string $path The path of asset config
     *
     * @return string
     */
    public static function getPackageName($type, $path)
    {
        return '@'.$type.'/'.basename(dirname($path));
    }

    /**
     * Find the native asset packages.
     *
     * @param string $type      The asset type
     * @param string $filename  The filename of asset config
     * @param string $directory The installation directory of assets
     *
     * @return array The map of package name and path
     */
    public static function findPackages($type, $filename, $directory)
    {
        $packages = array();
        $finder = Finder::create()->ignoreVCS(true)->ignoreDotFiles(false)->depth(1);
        $finder->name($filename);

        if (is_dir($directory)) {
            $paths = iterator_to_array($finder->in($directory));

            /* @var SplFileInfo $file */
            foreach ($paths as $file) {
                $path = dirname($file->getRealPath());
                $name = static::getPackageName($type, $file->getRealPath());

                $packages[$name] = $path;
            }
        }

        return $packages;
    }
}
