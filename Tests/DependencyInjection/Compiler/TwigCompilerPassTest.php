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

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\TwigCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Bundle Assets Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TwigCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var TwigCompilerPass
     */
    protected $pass;

    protected function setUp()
    {
        $this->rootDir = sys_get_temp_dir().'/require_asset_bundle_twig_tag_pass_tests';
        $this->fs = new Filesystem();
        $this->pass = new TwigCompilerPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->rootDir);
        $this->pass = null;
    }

    public function testProcess()
    {
        $container = $this->getContainer();

        $definition = new Definition('Fxp\Component\RequireAsset\Tag\RequireStyleTag', array('test_require_tag'));
        $definition->setPublic(false);
        $definition->addTag('fxp_require_asset.require_tag');
        $container->setDefinition('twig.test_require_tag', $definition);

        $twigExtension = $container->getDefinition('twig.extension.fxp_require_asset');

        $this->assertCount(0, $twigExtension->getMethodCalls());
        $this->pass->process($container);
        $this->assertCount(1, $twigExtension->getMethodCalls());
    }

    /**
     * Gets the container.
     *
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.cache_dir' => $this->rootDir.'/cache',
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel_',
            'kernel.root_dir' => $this->rootDir,
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => false,
            'kernel.bundles' => array(),
        )));

        $aeDef = new Definition('Fxp\Component\RequireAsset\Twig\Extension\AssetExtension');
        $container->setDefinition('twig.extension.fxp_require_asset', $aeDef);

        return $container;
    }
}
