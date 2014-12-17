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
use Fxp\Bundle\RequireAssetBundle\Assetic\Filter\LessVariableFilter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Less Variable Filter Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class LessVariableFilterTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $content = '@content = "content";';
        $filter = new LessVariableFilter();

        $this->assertNull($filter->container);
        $filter->container = $this->getContainer();

        $asset = new StringAsset($content, array($filter));
        $asset->dump();

        $validContent = '@asset-package1-path: "path_to_package1";'.PHP_EOL
            .'@asset-package2-path: "path_to_package2";'.PHP_EOL
            .'@vendor-asset-bundle-path: "path_to_bundle";'.PHP_EOL
            .$content;

        $this->assertEquals($validContent, $asset->getContent());
    }

    public function testGetHash()
    {
        $filter = new LessVariableFilter();
        $filter->container = $this->getContainer();
        $validFilter = new LessVariableFilter($filter->container->getParameter('fxp_require_asset.package_dirs'));

        $this->assertSame(serialize($validFilter), $filter->hash());
    }

    /**
     * Gets the container.
     *
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'       => false,
            'kernel.environment' => 'test',
            'kernel.name'        => 'kernel',
            'kernel.root_dir'    => __DIR__,
            'kernel.charset'     => 'UTF-8',
            'assetic.debug'      => false,
            'fxp_require_asset.package_dirs' => array(
                '@asset/package1'     => 'path_to_package1',
                '@asset/package2'     => 'path_to_package2',
                'vendor_asset_bundle' => 'path_to_bundle',
            ),
        )));

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
