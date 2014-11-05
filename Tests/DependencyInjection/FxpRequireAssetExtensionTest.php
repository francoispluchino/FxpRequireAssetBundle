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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Bundle Extension Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpRequireAssetExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $cacheDir;

    protected function setUp()
    {
        $this->cacheDir = sys_get_temp_dir() . '/require_asset_tests';
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

    /**
     * Gets the container.
     *
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.cache_dir'   => $this->cacheDir,
            'kernel.debug'       => false,
            'kernel.environment' => 'test',
            'kernel.name'        => 'kernel',
            'kernel.root_dir'    => __DIR__,
            'kernel.charset'     => 'UTF-8',
            'assetic.debug'      => false,
            'kernel.bundles'     => array(),
        )));

        $asseticManager = new Definition('Assetic\Factory\LazyAssetManager');
        $container->setDefinition('assetic.asset_manager', $asseticManager);

        $bundle = new FxpRequireAssetBundle();
        $bundle->build($container); // Attach all default factories

        $extension = new FxpRequireAssetExtension();
        $container->registerExtension($extension);
        $config = array();
        $extension->load(array($config), $container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
