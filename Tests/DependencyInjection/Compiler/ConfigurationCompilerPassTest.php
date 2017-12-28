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

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\ConfigurationCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Configuration Compiler Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationCompilerPassTest extends TestCase
{
    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var string
     */
    protected $servicePrefix;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var ConfigurationCompilerPass
     */
    protected $pass;

    protected function setUp()
    {
        $this->projectDir = sys_get_temp_dir().'/require_asset_bundle_assets_pass_tests';
        $this->servicePrefix = 'fxp_require_asset.assetic.config.';
        $this->fs = new Filesystem();
        $this->pass = new ConfigurationCompilerPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->projectDir);
        $this->projectDir = null;
        $this->fs = null;
        $this->pass = null;
        $this->servicePrefix = null;
    }

    public function testProcess()
    {
        $container = $this->getContainer();
        $configs = [
            ['file_extension_manager', 'file_extensions', 'addDefaultExtensions'],
            ['pattern_manager',        'patterns',        'addDefaultPatterns'],
            ['output_manager',         'output_rewrites', 'addOutputPatterns'],
            ['package_manager',        'packages',        'addPackages'],
        ];

        foreach ($configs as $config) {
            $this->assertTrue($container->hasDefinition($this->servicePrefix.$config[0]));
            $this->assertTrue($container->hasParameter($this->servicePrefix.$config[1]));
            $this->assertCount(0, $container->getDefinition($this->servicePrefix.$config[0])->getMethodCalls());
        }

        $this->pass->process($container);

        foreach ($configs as $config) {
            $this->assertTrue($container->hasDefinition($this->servicePrefix.$config[0]));
            $this->assertFalse($container->hasParameter($this->servicePrefix.$config[1]));
            $methodCalls = $container->getDefinition($this->servicePrefix.$config[0])->getMethodCalls();
            $this->assertCount(1, $methodCalls);

            foreach ($methodCalls as $methodCall) {
                $this->assertSame($config[2], $methodCall[0]);
            }
        }
    }

    public function testProcessWithoutAssetic()
    {
        $container = $this->getContainer(false);
        $configs = [
            ['file_extension_manager', 'file_extensions', 'addDefaultExtensions'],
            ['pattern_manager',        'patterns',        'addDefaultPatterns'],
            ['output_manager',         'output_rewrites', 'addOutputPatterns'],
            ['package_manager',        'packages',        'addPackages'],
        ];

        foreach ($configs as $config) {
            $this->assertFalse($container->hasDefinition($this->servicePrefix.$config[0]));
            $this->assertFalse($container->hasParameter($this->servicePrefix.$config[1]));
        }

        $this->pass->process($container);

        foreach ($configs as $config) {
            $this->assertFalse($container->hasDefinition($this->servicePrefix.$config[0]));
            $this->assertFalse($container->hasParameter($this->servicePrefix.$config[1]));
        }
    }

    /**
     * Gets the container.
     *
     * @param bool $managers
     *
     * @return ContainerBuilder
     */
    protected function getContainer($managers = true)
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

        if ($managers) {
            $this->configureManager($container, 'file_extension_manager', 'file_extensions');
            $this->configureManager($container, 'pattern_manager', 'patterns');
            $this->configureManager($container, 'output_manager', 'output_rewrites');
            $this->configureManager($container, 'package_manager', 'packages');
        }

        $this->configureManager($container, 'asset_replacement_manager', 'asset_replacement', 'fxp_require_asset.config.');

        return $container;
    }

    /**
     * Configure the asset package section.
     *
     * @param ContainerBuilder $container
     * @param string           $idManager
     * @param string           $idParameters
     * @param string|null      $servicePrefix
     */
    protected function configureManager(ContainerBuilder $container, $idManager, $idParameters, $servicePrefix = null)
    {
        $servicePrefix = null !== $servicePrefix ? $servicePrefix : $this->servicePrefix;
        $managerDef = new Definition('Foobar\Manager');
        $container->setDefinition($servicePrefix.$idManager, $managerDef);
        $container->setParameter($servicePrefix.$idParameters, []);
    }
}
