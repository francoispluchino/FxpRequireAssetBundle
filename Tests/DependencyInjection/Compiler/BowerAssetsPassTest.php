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

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\BowerAssetsPass;

/**
 * Bower Assets Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BowerAssetsPassTest extends BaseNativeAssetsPassTest
{
    /**
     * {@inheritdoc}
     */
    protected function getCompilerPass()
    {
        return new BowerAssetsPass();
    }

    /**
     * {@inheritdoc}
     */
    protected function getTmpRootDir()
    {
        return '/require_asset_bower_assets_pass_tests';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigOptionName()
    {
        return 'native_bower';
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstallDir()
    {
        return 'vendor/bower-native';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageFilename()
    {
        return '.bower.json';
    }

    /**
     * {@inheritdoc}
     */
    protected function createInstalledPackages()
    {
        parent::createInstalledPackages();

        $bowerrc = array(
            'directory' => $this->getInstallDir(),
        );

        $this->fs->dumpFile($this->rootDir.'/.bowerrc', json_encode($bowerrc));
    }
}
