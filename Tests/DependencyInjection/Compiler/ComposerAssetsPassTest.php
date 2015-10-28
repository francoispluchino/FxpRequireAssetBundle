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

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\ComposerAssetsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Composer Assets Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ComposerAssetsPassTest extends \PHPUnit_Framework_TestCase
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
     * @var ComposerAssetsPass
     */
    protected $pass;

    protected function setUp()
    {
        $this->rootDir = sys_get_temp_dir().'/require_asset_composer_assets_pass_tests';
        $this->fs = new Filesystem();
        $this->pass = new ComposerAssetsPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->rootDir);
        $this->pass = null;
    }

    public function testProcessWithNotComposerFile()
    {
        $container = $this->getContainer();
        $this->pass->process($container);

        $def = $container->getDefinition('fxp_require_asset.assetic.config.package_manager');
        $this->assertCount(0, $def->getMethodCalls());
    }

    public function getComposerConfig()
    {
        return array(
            array(array()),
            array(array(
                'extra' => array(
                    'asset-installer-paths' => array(
                        'npm-asset-library' => 'custom',
                        'bower-asset-library' => 'custom',
                    ),
                ),
            )),
        );
    }

    /**
     * @dataProvider getComposerConfig
     *
     * @param array $composer
     */
    public function testProcessWithPackages(array $composer)
    {
        $this->fs->dumpFile($this->rootDir.'/composer.json', json_encode($composer));
        $this->createInstalledPackages();
        $container = $this->getContainer();
        $this->pass->process($container);

        $def = $container->getDefinition('fxp_require_asset.assetic.config.package_manager');
        $this->assertCount(2, $def->getMethodCalls());
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

        $container->setParameter('fxp_require_asset.base_dir', $this->rootDir);
        $container->setParameter('fxp_require_asset.composer_installed_path', $this->rootDir.'/vendor/composer/installed.json');

        $pmDef = new Definition('Fxp\Component\RequireAsset\Assetic\Config\PackageManager');
        $container->setDefinition('fxp_require_asset.assetic.config.package_manager', $pmDef);

        $asseticManager = new Definition('Assetic\Factory\LazyAssetManager');
        $container->setDefinition('assetic.asset_manager', $asseticManager);

        return $container;
    }

    protected function createInstalledPackages()
    {
        $installed = array(
            array(
                'name' => 'acme/demo',
                'version' => '0.1.0',
                'type' => 'library',
                'require' => array(
                    'bower-asset/foobar' => '1.0.0',
                    'npm-asset/barfoo' => '1.0.0',
                ),
            ),
            array(
                'name' => 'bower-asset/foobar',
                'version' => '1.0.0',
                'type' => 'bower-asset-library',
            ),
            array(
                'name' => 'npm-asset/barfoo',
                'version' => '2.3.0',
                'type' => 'npm-asset-library',
            ),
        );

        $this->fs->dumpFile($this->rootDir.'/vendor/composer/installed.json', json_encode($installed));
    }
}
