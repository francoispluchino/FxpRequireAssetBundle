<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\DependencyInjection;

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\BundleAssetsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Bundle Assets Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BundleAssetsPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var BundleAssetsPass
     */
    protected $pass;

    protected function setUp()
    {
        $this->rootDir = sys_get_temp_dir().'/require_asset_bundle_assets_pass_tests';
        $this->fs = new Filesystem();
        $this->pass = new BundleAssetsPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->rootDir);
        $this->pass = null;
    }

    public function getBundles()
    {
        return array(
            array(array()),
            array(array(
                'FxpRequireAssetBundle' => 'Fxp\\Bundle\\RequireAssetBundle\\FxpRequireAssetBundle',
            )),
        );
    }

    /**
     * @dataProvider getBundles
     * @param array $bundles
     */
    public function testProcess(array $bundles)
    {
        $container = $this->getContainer($bundles);

        $def = $container->getDefinition('fxp_require_asset.assetic.config.package_manager');

        $this->assertCount(0, $def->getMethodCalls());
        $this->pass->process($container);
        $this->assertCount(count($bundles), $def->getMethodCalls());

        foreach ($def->getMethodCalls() as $methodDef) {
            $this->assertSame('addPackage', $methodDef[0]);
            $this->assertArrayHasKey('name', $methodDef[1][0]);
            $this->assertArrayHasKey('source_path', $methodDef[1][0]);
            $this->assertArrayHasKey('source_base', $methodDef[1][0]);
            $this->assertArrayHasKey('patterns', $methodDef[1][0]);
        }
    }

    /**
     * Gets the container.
     *
     * @param array $bundles
     *
     * @return ContainerBuilder
     */
    protected function getContainer(array $bundles)
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.cache_dir'   => $this->rootDir.'/cache',
            'kernel.debug'       => false,
            'kernel.environment' => 'test',
            'kernel.name'        => 'kernel',
            'kernel.root_dir'    => $this->rootDir,
            'kernel.charset'     => 'UTF-8',
            'assetic.debug'      => false,
            'kernel.bundles'     => $bundles,
        )));

        $pmDef = new Definition('Fxp\Component\RequireAsset\Assetic\Config\PackageManager');
        $container->setDefinition('fxp_require_asset.assetic.config.package_manager', $pmDef);

        return $container;
    }
}
