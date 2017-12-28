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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Custom Assets Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CustomAssetsPassTest extends TestCase
{
    /**
     * @var string
     */
    protected $projectDir;

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
        $this->projectDir = sys_get_temp_dir().'/require_asset_custom_assets_pass_tests';
        $this->fs = new Filesystem();
        $this->pass = new CustomAssetsPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->projectDir);
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
            ->addArgument(['input_filename.ext'])
        ;
        $container->setDefinition('asset_test', $def);

        $this->pass->process($container);
        $this->assertTrue($container->hasParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));

        $file = $container->getParameter('kernel.cache_dir').'/fxp_require_asset/path.ext';
        $valid = <<<'EOF'
@import "input_filename.ext";
EOF;

        $this->assertFileExists($file);
        $this->assertSame($valid, file_get_contents($file));
    }

    public function testProcessWithCustomVariables()
    {
        $container = $this->getContainer();
        $this->addCustomVariables($container);
        $validVariables = [
            'custom-variable1' => 'value1',
            'custom-variable2' => 'value2',
        ];
        $this->assertTrue($container->hasParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));
        $this->assertSame($validVariables, $container->getParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));

        $def = new Definition();
        $def
            ->setSynthetic(true)
            ->addTag('fxp_require_asset.assetic.custom_asset')
            ->addArgument('path.ext')
            ->addArgument(['input_filename.ext'])
            ->addArgument('variable-name')
        ;
        $container->setDefinition('asset_test', $def);

        $this->pass->process($container);
        $this->assertTrue($container->hasParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));

        $file = $container->getParameter('kernel.cache_dir').'/fxp_require_asset/path.ext';
        $valid = <<<'EOF'
@import "input_filename.ext";
EOF;

        $this->assertFileExists($file);
        $this->assertSame($valid, file_get_contents($file));

        $validVariables = array_merge($validVariables, ['variable-name' => $file]);
        $this->assertSame($validVariables, $container->getParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables'));
    }

    /**
     * @expectedException \Fxp\Component\RequireAsset\Exception\InvalidArgumentException
     * @expectedExceptionMessage The argument 1 "filename" is required and must be a string for the "asset_test" service
     */
    public function testInvalidFirstArgument()
    {
        $container = $this->getContainer();

        $def = new Definition();
        $def
            ->setSynthetic(true)
            ->addTag('fxp_require_asset.assetic.custom_asset')
        ;
        $container->setDefinition('asset_test', $def);

        $this->pass->process($container);
    }

    /**
     * @expectedException \Fxp\Component\RequireAsset\Exception\InvalidArgumentException
     * @expectedExceptionMessage The argument 2 "inputs" is required and must be a array for the "asset_test" service
     */
    public function testInvalidSecondArgument()
    {
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
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.cache_dir' => $this->projectDir.'/cache',
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.project_dir' => $this->projectDir,
            'kernel.root_dir' => $this->projectDir.'/src',
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => false,
            'assetic.cache_dir' => $this->projectDir.'/cache/assetic',
            'kernel.bundles' => [],
        ]));

        return $container;
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addCustomVariables(ContainerBuilder $container)
    {
        $variables = [
            'custom-variable1' => 'value1',
            'custom-variable2' => 'value2',
        ];

        $container->setParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables', $variables);
    }
}
