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

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\FxpRequireAssetExtension;
use Fxp\Bundle\RequireAssetBundle\FxpRequireAssetBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Bundle Extension Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpRequireAssetExtensionTest extends TestCase
{
    /**
     * @var string
     */
    protected $cacheDir;

    protected function setUp()
    {
        $this->cacheDir = sys_get_temp_dir().'/require_asset_tests';
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);
    }

    public function testCompileContainerWithExtension()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->hasDefinition('twig.extension.fxp_require_asset'));
    }

    public function testRemoveDisabledCommonAssets()
    {
        $config = [
            'common_assets' => [
                'common_css' => [
                    'output' => '/js/common.js',
                    'filters' => [],
                    'inputs' => [
                        '@acme_demo/js/asset.js',
                    ],
                    'options' => [
                        'disabled' => true,
                    ],
                ],
            ],
        ];

        $this->assertInstanceOf(ContainerBuilder::class, $this->getContainer($config));
    }

    public function testDebugCommonAssets()
    {
        $config = [
            'common_assets' => [
                'common_css' => [
                    'output' => '/js/common.js',
                    'filters' => [],
                    'inputs' => [
                        '@acme_demo/js/asset.js',
                    ],
                    'options' => [
                        'require_debug' => true,
                    ],
                ],
            ],
        ];

        $this->assertInstanceOf(ContainerBuilder::class, $this->getContainer($config, true));
    }

    public function testWebpackCache()
    {
        $config = [
            'webpack' => [
                'cache' => [
                    'enabled' => true,
                ],
            ],
        ];

        $this->assertInstanceOf(ContainerBuilder::class, $this->getContainer($config, true));
    }

    public function testNotAddCompilerForKernelNameWithoutUnderscore()
    {
        $container = $this->getContainer([], false, 'kernel_');
        $this->assertGreaterThan(1, count($container->getCompilerPassConfig()->getPasses()));

        $container = $this->getContainer([], false, 'kernel');
        $this->assertGreaterThan(1, $container->getCompilerPassConfig()->getPasses());
    }

    /**
     * Gets the container.
     *
     * @param array  $config     The container config
     * @param bool   $debug      The debug mode
     * @param string $kernelName The name of kernel
     *
     * @return ContainerBuilder
     */
    protected function getContainer(array $config = [], $debug = false, $kernelName = 'kernel')
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.cache_dir' => $this->cacheDir,
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => $kernelName,
            'kernel.project_dir' => __DIR__,
            'kernel.root_dir' => __DIR__.'/src',
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => $debug,
            'kernel.bundles' => [],
            'assetic.cache_dir' => $this->cacheDir.'/assetic',
            'locale' => 'en',
        ]));

        $asseticManager = new Definition('Assetic\Factory\LazyAssetManager');
        $container->setDefinition('assetic.asset_manager', $asseticManager);

        $bundle = new FxpRequireAssetBundle();
        $bundle->build($container); // Attach all default factories

        $extension = new FxpRequireAssetExtension();
        $container->registerExtension($extension);
        $extension->load([$config], $container);

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();

        return $container;
    }
}
