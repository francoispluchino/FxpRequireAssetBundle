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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Asset Extension Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AssetExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testContainerServiceWithoutContainerRenderers()
    {
        $mess = 'The service definition "twig.extension.fxp_require_asset.container_tag_renderers" does not exist';
        $this->setExpectedException('Symfony\Component\DependencyInjection\Exception\InvalidArgumentException', $mess);

        $ext = new AssetExtension();

        $this->assertNull($ext->container);
        $this->assertCount(0, $ext->getRenderers());
        $ext->container = $this->getContainer();

        $ext->renderTags();
    }

    public function testContainerService()
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
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => false,
        )));

        if ($useContainerRenderers) {
            $renderer = new Definition($this->getMockClass('Fxp\Component\RequireAsset\Tag\Renderer\TagRendererInterface'));
            $container->setDefinition('fxp_require_asset_test.twig.mock_renderer', $renderer);

            $renderers = new Definition('Fxp\Bundle\RequireAssetBundle\Twig\Extension\ContainerRenderers');
            $renderers->addMethodCall('addRenderer', array(new Reference('fxp_require_asset_test.twig.mock_renderer')));
            $container->setDefinition('twig.extension.fxp_require_asset.container_tag_renderers', $renderers);
        }

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
