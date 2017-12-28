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

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\BundleAssetsPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Bundle Assets Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BundleAssetsPassTest extends TestCase
{
    /**
     * @var string
     */
    protected $projectDir;

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
        $this->projectDir = sys_get_temp_dir().'/require_asset_bundle_assets_pass_tests';
        $this->fs = new Filesystem();
        $this->pass = new BundleAssetsPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->projectDir);
        $this->pass = null;
    }

    public function getBundles()
    {
        return [
            [[]],
            [[
                'FxpRequireAssetBundle' => 'Fxp\\Bundle\\RequireAssetBundle\\FxpRequireAssetBundle',
            ]],
        ];
    }

    /**
     * @dataProvider getBundles
     *
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
     * @dataProvider getBundles
     *
     * @param array $bundles
     */
    public function testProcessWithoutAssetic(array $bundles)
    {
        $container = $this->getContainer($bundles, false);

        $def = $container->getDefinition('fxp_require_asset.assetic.config.package_manager');

        $this->assertCount(0, $def->getMethodCalls());
        $this->pass->process($container);
        $this->assertCount(0, $def->getMethodCalls());
    }

    /**
     * Gets the container.
     *
     * @param array $bundles
     * @param bool  $assetic
     *
     * @return ContainerBuilder
     */
    protected function getContainer(array $bundles, $assetic = true)
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.cache_dir' => $this->projectDir.'/cache',
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.project_dir' => $this->projectDir,
            'kernel.root_dir' => $this->projectDir.'/src',
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => false,
            'assetic.cache_dir' => $this->projectDir.'/cache/assetic',
            'kernel.bundles' => $bundles,
            'fxp_require_asset.assetic' => $assetic,
        ]));

        $pmDef = new Definition('Fxp\Component\RequireAsset\Assetic\Config\PackageManager');
        $container->setDefinition('fxp_require_asset.assetic.config.package_manager', $pmDef);

        return $container;
    }
}
