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

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\CustomAssetsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Custom Assets Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CustomAssetsPassTest extends \PHPUnit_Framework_TestCase
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
     * @var CustomAssetsPass
     */
    protected $pass;

    protected function setUp()
    {
        $this->rootDir = sys_get_temp_dir().'/require_asset_custom_assets_pass_tests';
        $this->fs = new Filesystem();
        $this->pass = new CustomAssetsPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->rootDir);
        $this->pass = null;
    }

    public function testProcess()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->hasParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));
        $this->pass->process($container);
        $this->assertTrue($container->hasParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));
    }

    public function testProcessWithoutCustomVariables()
    {
        $container = $this->getContainer();
        $this->assertFalse($container->hasParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));

        $def = new Definition();
        $def
            ->setSynthetic(true)
            ->addTag('fxp_require_asset.assetic.custom_asset')
            ->addArgument('path.ext')
            ->addArgument(array('input_filename.ext'))
        ;
        $container->setDefinition('asset_test', $def);

        $this->pass->process($container);
        $this->assertTrue($container->hasParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));

        $file = $container->getParameter('kernel.cache_dir').'/fxp_require_asset/path.ext';
        $valid = <<<EOF
@import "input_filename.ext";
EOF;

        $this->assertTrue(file_exists($file));
        $this->assertSame($valid, file_get_contents($file));
    }

    public function testProcessWithCustomVariables()
    {
        $container = $this->getContainer();
        $this->addCustomVariables($container);
        $validVariables = array(
            'custom-variable1' => 'value1',
            'custom-variable2' => 'value2',
        );
        $this->assertTrue($container->hasParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));
        $this->assertSame($validVariables, $container->getParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));

        $def = new Definition();
        $def
            ->setSynthetic(true)
            ->addTag('fxp_require_asset.assetic.custom_asset')
            ->addArgument('path.ext')
            ->addArgument(array('input_filename.ext'))
            ->addArgument('variable-name')
        ;
        $container->setDefinition('asset_test', $def);

        $this->pass->process($container);
        $this->assertTrue($container->hasParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));

        $file = $container->getParameter('kernel.cache_dir').'/fxp_require_asset/path.ext';
        $valid = <<<EOF
@import "input_filename.ext";
EOF;

        $this->assertTrue(file_exists($file));
        $this->assertSame($valid, file_get_contents($file));

        $validVariables = array_merge($validVariables, array('variable-name' => $file));
        $this->assertSame($validVariables, $container->getParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));
    }

    public function testInvalidFirstArgument()
    {
        $this->setExpectedException('Fxp\Component\RequireAsset\Exception\InvalidArgumentException', 'The argument 1 "filename" is required and must be a string for the "asset_test" service');
        $container = $this->getContainer();

        $def = new Definition();
        $def
            ->setSynthetic(true)
            ->addTag('fxp_require_asset.assetic.custom_asset')
        ;
        $container->setDefinition('asset_test', $def);

        $this->pass->process($container);
    }

    public function testInvalidSecondArgument()
    {
        $this->setExpectedException('Fxp\Component\RequireAsset\Exception\InvalidArgumentException', 'The argument 2 "inputs" is required and must be a array for the "asset_test" service');
        $container = $this->getContainer();

        $def = new Definition();
        $def
            ->setSynthetic(true)
            ->addTag('fxp_require_asset.assetic.custom_asset')
            ->addArgument('path.ext')
        ;
        $container->setDefinition('asset_test', $def);

        $this->pass->process($container);
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

        return $container;
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addCustomVariables(ContainerBuilder $container)
    {
        $variables = array(
            'custom-variable1' => 'value1',
            'custom-variable2' => 'value2',
        );

        $container->setParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables', $variables);
    }
}
