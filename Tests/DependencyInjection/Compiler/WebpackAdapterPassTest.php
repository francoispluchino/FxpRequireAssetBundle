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

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\WebpackAdapterPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Webpack Adapter Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class WebpackAdapterPassTest extends TestCase
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
     * @var WebpackAdapterPass
     */
    protected $pass;

    protected function setUp()
    {
        $this->projectDir = sys_get_temp_dir().'/require_asset_webpack_adapter_pass_tests';
        $this->fs = new Filesystem();
        $this->pass = new WebpackAdapterPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->projectDir);
        $this->pass = null;
    }

    public function testProcessAutoManifestAdapter()
    {
        $container = $this->getContainer();

        $assertVersionDef = new Definition(\stdClass::class, ['%kernel.project_dir%/manifest.json']);
        $container->setDefinition('assets._version__default', $assertVersionDef);

        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->pass->process($container);

        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter'));
        $this->assertTrue($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->assertFalse($container->hasDefinition('fxp_require_asset.webpack.adapter.assets'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.assets.file'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.assets.cache_key'));
        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.assets.cache'));

        $this->assertTrue($container->hasDefinition('fxp_require_asset.webpack.adapter.manifest'));
        $this->assertTrue($container->hasParameter('fxp_require_asset.webpack.adapter.manifest.file'));
    }

    public function testProcessAutoAssetsAdapter()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->pass->process($container);

        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter'));
        $this->assertTrue($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->assertTrue($container->hasDefinition('fxp_require_asset.webpack.adapter.assets'));
        $this->assertTrue($container->hasParameter('fxp_require_asset.webpack.adapter.assets.file'));
        $this->assertTrue($container->hasParameter('fxp_require_asset.webpack.adapter.assets.cache_key'));
        $this->assertTrue($container->hasAlias('fxp_require_asset.webpack.adapter.assets.cache'));

        $this->assertFalse($container->hasDefinition('fxp_require_asset.webpack.adapter.manifest'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.manifest.file'));
    }

    public function testProcessManifestAdapter()
    {
        $container = $this->getContainer('manifest');

        $assertVersionDef = new Definition(\stdClass::class, ['%kernel.project_dir%/manifest.json']);
        $container->setDefinition('assets._version__default', $assertVersionDef);

        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->pass->process($container);

        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter'));
        $this->assertTrue($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->assertFalse($container->hasDefinition('fxp_require_asset.webpack.adapter.assets'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.assets.file'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.assets.cache_key'));
        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.assets.cache'));

        $this->assertTrue($container->hasDefinition('fxp_require_asset.webpack.adapter.manifest'));
        $this->assertTrue($container->hasParameter('fxp_require_asset.webpack.adapter.manifest.file'));
    }

    public function testProcessMockAdapterForTestEnv()
    {
        $container = $this->getContainer('manifest', '%kernel.project_dir%/manifest.json', 'test');

        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->pass->process($container);

        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter'));
        $this->assertTrue($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->assertFalse($container->hasDefinition('fxp_require_asset.webpack.adapter.assets'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.assets.file'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.assets.cache_key'));
        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.assets.cache'));

        $this->assertFalse($container->hasDefinition('fxp_require_asset.webpack.adapter.manifest'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.manifest.file'));

        $this->assertTrue($container->hasDefinition('fxp_require_asset.webpack.adapter.mock'));
    }

    public function testProcessManifestAdapterManual()
    {
        $container = $this->getContainer('manifest', '%kernel.project_dir%/manifest.json');

        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->pass->process($container);

        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter'));
        $this->assertTrue($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->assertFalse($container->hasDefinition('fxp_require_asset.webpack.adapter.assets'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.assets.file'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.assets.cache_key'));
        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.assets.cache'));

        $this->assertTrue($container->hasDefinition('fxp_require_asset.webpack.adapter.manifest'));
        $this->assertTrue($container->hasParameter('fxp_require_asset.webpack.adapter.manifest.file'));
    }

    /**
     * @expectedException \Fxp\Component\RequireAsset\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "fxp_require_asset.webpack.manifest_adapter.file" option or "framework.assets.json_manifest_path" is required to use the webpack manifest adapter
     */
    public function testProcessManifestAdapterWithoutFile()
    {
        $container = $this->getContainer('manifest');

        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->pass->process($container);

        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter'));
        $this->assertTrue($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->assertFalse($container->hasDefinition('fxp_require_asset.webpack.adapter.assets'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.assets.file'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.assets.cache_key'));
        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.assets.cache'));

        $this->assertTrue($container->hasDefinition('fxp_require_asset.webpack.adapter.manifest'));
        $this->assertTrue($container->hasParameter('fxp_require_asset.webpack.adapter.manifest.file'));
    }

    public function testProcessAssetsAdapter()
    {
        $container = $this->getContainer('assets');

        $assertVersionDef = new Definition(\stdClass::class, ['%kernel.project_dir%/manifest.json']);
        $container->setDefinition('assets._version__default', $assertVersionDef);

        $this->assertFalse($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->pass->process($container);

        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter'));
        $this->assertTrue($container->hasAlias('fxp_require_asset.webpack.adapter.default'));

        $this->assertTrue($container->hasDefinition('fxp_require_asset.webpack.adapter.assets'));
        $this->assertTrue($container->hasParameter('fxp_require_asset.webpack.adapter.assets.file'));
        $this->assertTrue($container->hasParameter('fxp_require_asset.webpack.adapter.assets.cache_key'));
        $this->assertTrue($container->hasAlias('fxp_require_asset.webpack.adapter.assets.cache'));

        $this->assertFalse($container->hasDefinition('fxp_require_asset.webpack.adapter.manifest'));
        $this->assertFalse($container->hasParameter('fxp_require_asset.webpack.adapter.manifest.file'));
    }

    /**
     * Gets the container.
     *
     * @param string      $adapter
     * @param string|null $manifestFile
     * @param string      $env
     *
     * @return ContainerBuilder
     */
    protected function getContainer($adapter = 'auto', $manifestFile = null, $env = 'dev')
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.cache_dir' => $this->projectDir.'/cache',
            'kernel.debug' => false,
            'kernel.environment' => $env,
            'kernel.name' => 'kernel',
            'kernel.project_dir' => $this->projectDir,
            'kernel.root_dir' => $this->projectDir.'/src',
            'kernel.charset' => 'UTF-8',
            'kernel.bundles' => [],
        ]));

        $container->setParameter('fxp_require_asset.webpack.adapter', $adapter);

        $container->setDefinition('fxp_require_asset.webpack.adapter.manifest', new Definition(\stdClass::class));
        $container->setParameter('fxp_require_asset.webpack.adapter.manifest.file', $manifestFile);

        $container->setDefinition('fxp_require_asset.webpack.adapter.assets', new Definition(\stdClass::class));
        $container->setParameter('fxp_require_asset.webpack.adapter.assets.file', '%kernel.project_dir%/assets.json');
        $container->setParameter('fxp_require_asset.webpack.adapter.assets.cache_key', 'assets_cache_key');
        $container->setAlias('fxp_require_asset.webpack.adapter.assets.cache', 'app.cache');

        return $container;
    }
}
