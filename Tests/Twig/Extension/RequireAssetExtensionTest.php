<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\Twig\Extension;

use Fxp\Bundle\RequireAssetBundle\Twig\Extension\RequireAssetExtension;
use Fxp\Component\RequireAsset\Asset\ChainRequireAssetManager;
use Fxp\Component\RequireAsset\Webpack\Adapter\ManifestAdapter;
use Fxp\Component\RequireAsset\Webpack\WebpackRequireAssetManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Asset Extension Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class RequireAssetExtensionTest extends TestCase
{
    public function testContainerServiceWithoutContainerRenderers(): void
    {
        $this->expectException(\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('You have requested a non-existent service "fxp_require_asset.chain_require_asset_manager".');

        $ext = new RequireAssetExtension();

        $this->assertNull($ext->container);
        $ext->container = $this->getContainer();

        $this->assertSame('@acme_demo/js/asset2.js', $ext->requireAsset('@acme_demo/js/asset2.js'));
    }

    public function testContainerService(): void
    {
        $ext = new RequireAssetExtension();

        $this->assertNull($ext->container);
        $ext->container = $this->getContainer(true);

        $this->assertSame('@acme_demo/js/asset.js', $ext->requireAsset('@acme_demo/js/asset.js'));
        $this->assertNull($ext->container);
    }

    /**
     * Gets the container.
     *
     * @param bool $useContainer
     *
     * @return ContainerBuilder
     */
    protected function getContainer($useContainer = false)
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.project_dir' => __DIR__,
            'kernel.root_dir' => __DIR__.'/src',
            'kernel.charset' => 'UTF-8',
        ]));

        if ($useContainer) {
            $assetAdapter = new Definition(ManifestAdapter::class);
            $assetAdapter->setArguments([__DIR__.'/../../../vendor/fxp/require-asset/Tests/Fixtures/Webpack/manifest.json']);
            $container->setDefinition('fxp_require_asset.require_asset_manager.adapter', $assetAdapter);

            $assetManager = new Definition(WebpackRequireAssetManager::class);
            $assetManager->setArguments([new Reference('fxp_require_asset.require_asset_manager.adapter')]);
            $container->setDefinition('fxp_require_asset.require_asset_manager', $assetManager);

            $chainAssetManager = new Definition(ChainRequireAssetManager::class);
            $chainAssetManager->setArguments([[new Reference('fxp_require_asset.require_asset_manager')]]);
            $chainAssetManager->setPublic(true);
            $container->setDefinition('fxp_require_asset.chain_require_asset_manager', $chainAssetManager);
        }

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();

        return $container;
    }
}
