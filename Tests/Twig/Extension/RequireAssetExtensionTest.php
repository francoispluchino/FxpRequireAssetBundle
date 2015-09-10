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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Asset Extension Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RequireAssetExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testContainerServiceWithoutContainerRenderers()
    {
        $mess = 'The service definition "assetic.asset_manager" does not exist';
        $this->setExpectedException('Symfony\Component\DependencyInjection\Exception\InvalidArgumentException', $mess);

        $ext = new RequireAssetExtension();

        $this->assertNull($ext->container);
        $ext->container = $this->getContainer();

        $this->assertSame('@acme_demo/js/asset2.js', $ext->requireAsset('@acme_demo/js/asset2.js'));
    }

    public function testContainerService()
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
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => false,
        )));

        if ($useContainer) {
            $asseticManager = new Definition('Assetic\AssetManager');
            $container->setDefinition('assetic.asset_manager', $asseticManager);
        }

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
