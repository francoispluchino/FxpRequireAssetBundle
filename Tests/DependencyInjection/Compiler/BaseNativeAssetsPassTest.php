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

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Base for native Assets Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class BaseNativeAssetsPassTest extends TestCase
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
     * @var CompilerPassInterface
     */
    protected $pass;

    protected function setUp()
    {
        $this->projectDir = sys_get_temp_dir().$this->getTmpRootDir();
        $this->fs = new Filesystem();
        $this->pass = $this->getCompilerPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->projectDir);
        $this->pass = null;
    }

    public function testProcessWithDisableConfig()
    {
        $container = $this->getContainer(false);
        $this->pass->process($container);

        $def = $container->getDefinition('fxp_require_asset.assetic.config.package_manager');
        $this->assertCount(0, $def->getMethodCalls());
    }

    public function testProcessWithPackages()
    {
        $this->createInstalledPackages();
        $container = $this->getContainer();
        $this->pass->process($container);

        $def = $container->getDefinition('fxp_require_asset.assetic.config.package_manager');
        $this->assertCount(2, $def->getMethodCalls());
    }

    /**
     * Gets the container.
     *
     * @param bool $active
     *
     * @return ContainerBuilder
     */
    protected function getContainer($active = true)
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

        $container->setParameter('fxp_require_asset.base_dir', $this->projectDir);
        $container->setParameter('fxp_require_asset.'.$this->getConfigOptionName(), $active);
        $container->setParameter('fxp_require_asset.assetic', true);

        $pmDef = new Definition('Fxp\Component\RequireAsset\Assetic\Config\PackageManager');
        $container->setDefinition('fxp_require_asset.assetic.config.package_manager', $pmDef);

        $asseticManager = new Definition('Assetic\Factory\LazyAssetManager');
        $container->setDefinition('assetic.asset_manager', $asseticManager);

        return $container;
    }

    /**
     * Create the installed packages.
     */
    protected function createInstalledPackages()
    {
        $foobar = [
            'name' => 'foobar',
        ];
        $barfoo = [
            'name' => 'bar-foo',
        ];

        $this->fs->dumpFile($this->projectDir.'/'.$this->getInstallDir().'/foobar/'.$this->getPackageFilename(), json_encode($foobar));
        $this->fs->dumpFile($this->projectDir.'/'.$this->getInstallDir().'/barfoo/'.$this->getPackageFilename(), json_encode($barfoo));
    }

    /**
     * Get the compiler pass.
     *
     * @return CompilerPassInterface
     */
    abstract protected function getCompilerPass();

    /**
     * Get the path of temp root dir.
     *
     * @return string
     */
    abstract protected function getTmpRootDir();

    /**
     * Get the name of config option.
     *
     * @return string
     */
    abstract protected function getConfigOptionName();

    /**
     * Get the package filename.
     *
     * @return string
     */
    abstract protected function getInstallDir();

    /**
     * Get the package filename.
     *
     * @return string
     */
    abstract protected function getPackageFilename();
}
