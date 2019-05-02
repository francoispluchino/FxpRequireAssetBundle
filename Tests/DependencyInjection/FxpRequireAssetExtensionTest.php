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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Bundle Extension Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class FxpRequireAssetExtensionTest extends TestCase
{
    /**
     * @var string
     */
    protected $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir().'/require_asset_tests';
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);
    }

    public function testCompileContainerWithExtension(): void
    {
        $container = $this->getContainer();
        $this->assertTrue($container->hasDefinition('twig.extension.fxp_require_asset'));
    }

    public function testWebpackAssetCache(): void
    {
        $config = [
            'webpack' => [
                'assets_adapter' => [
                    'cache' => [
                        'enabled' => true,
                    ],
                ],
            ],
        ];

        $this->assertInstanceOf(ContainerBuilder::class, $this->getContainer($config));
    }

    public function testNotAddCompilerForKernelNameWithoutUnderscore(): void
    {
        $container = $this->getContainer([], 'kernel_');
        $this->assertGreaterThan(1, \count($container->getCompilerPassConfig()->getPasses()));

        $container = $this->getContainer([], 'kernel');
        $this->assertGreaterThan(1, $container->getCompilerPassConfig()->getPasses());
    }

    /**
     * Gets the container.
     *
     * @param array  $config     The container config
     * @param string $kernelName The name of kernel
     *
     * @return ContainerBuilder
     */
    protected function getContainer(array $config = [], $kernelName = 'kernel')
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.cache_dir' => $this->cacheDir,
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => $kernelName,
            'kernel.project_dir' => __DIR__,
            'kernel.root_dir' => __DIR__.'/src',
            'kernel.charset' => 'UTF-8',
            'kernel.bundles' => [],
            'locale' => 'en',
        ]));

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
