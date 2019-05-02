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

use Fxp\Bundle\RequireAssetBundle\Twig\Extension\AssetExtension;
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
final class AssetExtensionTest extends TestCase
{
    public function testContainerServiceWithoutContainerRenderers(): void
    {
        $this->expectException(\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('You have requested a non-existent service "twig.extension.fxp_require_asset.container_tag_renderers".');

        $ext = new AssetExtension();

        $this->assertNull($ext->container);
        $this->assertCount(0, $ext->getRenderers());
        $ext->container = $this->getContainer();

        $ext->renderTags();
    }

    public function testContainerService(): void
    {
        $ext = new AssetExtension();

        $this->assertNull($ext->container);
        $this->assertCount(0, $ext->getRenderers());
        $ext->container = $this->getContainer(true);

        $ext->renderTags();

        $this->assertCount(1, $ext->getRenderers());
        $this->assertNull($ext->container);
    }

    /**
     * Gets the container.
     *
     * @param bool $useContainerRenderers
     *
     * @return ContainerBuilder
     */
    protected function getContainer($useContainerRenderers = false)
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.project_dir' => __DIR__,
            'kernel.root_dir' => __DIR__.'/src',
            'kernel.charset' => 'UTF-8',
        ]));

        if ($useContainerRenderers) {
            $renderer = new Definition($this->getMockClass('Fxp\Component\RequireAsset\Tag\Renderer\TagRendererInterface'));
            $container->setDefinition('fxp_require_asset_test.twig.mock_renderer', $renderer);

            $renderers = new Definition('Fxp\Bundle\RequireAssetBundle\Twig\Extension\ContainerRenderers');
            $renderers->addMethodCall('addRenderer', [new Reference('fxp_require_asset_test.twig.mock_renderer')]);
            $renderers->setPublic(true);
            $container->setDefinition('twig.extension.fxp_require_asset.container_tag_renderers', $renderers);
        }

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();

        return $container;
    }
}
