<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\Assetic\Filter;

use Assetic\Asset\StringAsset;
use Fxp\Bundle\RequireAssetBundle\Assetic\Filter\ParameterBagFilter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Parameter Bag Filter Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ParameterBagFilterTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $content = '@param1 = "%kernel.environment%";'.PHP_EOL
            .'@param2 = "%kernel.root_dir%/test/";'.PHP_EOL
            .'@param3 = "%kernel.name%";'.PHP_EOL;
        $filter = new ParameterBagFilter();

        $this->assertNull($filter->container);
        $filter->container = $this->getContainer();

        $asset = new StringAsset($content, array($filter));
        $asset->dump();

        $validContent = '@param1 = "test";'.PHP_EOL
            .'@param2 = "'.__DIR__.'/test/";'.PHP_EOL
            .'@param3 = "kernel";'.PHP_EOL;

        $this->assertEquals($validContent, $asset->getContent());
    }

    public function testGetHash()
    {
        $filter = new ParameterBagFilter();
        $filter->container = $this->getContainer();

        $filter->hash();
    }

    /**
     * Gets the container.
     *
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => false,
        )));

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
