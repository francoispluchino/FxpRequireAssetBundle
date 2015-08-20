<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\DependencyInjection\Compiler;

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\NpmAssetsPass;

/**
 * NPM Assets Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class NpmAssetsPassTest extends BaseNativeAssetsPassTest
{
    /**
     * {@inheritdoc}
     */
    protected function getCompilerPass()
    {
        return new NpmAssetsPass();
    }

    /**
     * {@inheritdoc}
     */
    protected function getTmpRootDir()
    {
        return '/require_asset_npm_assets_pass_tests';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigOptionName()
    {
        return 'native_npm';
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstallDir()
    {
        return 'node_modules';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageFilename()
    {
        return 'package.json';
    }
}
