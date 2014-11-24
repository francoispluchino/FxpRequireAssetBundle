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

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\CompilerAssetsPass;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\FxpRequireAssetExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Compiler Assets Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CompilerAssetsPassTest extends \PHPUnit_Framework_TestCase
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
     * @var CompilerAssetsPass
     */
    protected $pass;

    protected function setUp()
    {
        $this->rootDir = sys_get_temp_dir().'/require_asset_compiler_assets_pass_tests';
        $this->fs = new Filesystem();
        $this->pass = new CompilerAssetsPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->rootDir);
        $this->fs = null;
        $this->pass = null;
        $this->rootDir = null;
    }

    public function testProcess()
    {
        $this->createFixtures();
        $container = $this->getContainer();
        $this->includeAssetPackageDefinition($container);
        $this->includeCommonAssetDefinition($container);

        $managerDef = $container->getDefinition('assetic.asset_manager');
        $methodCalls = $managerDef->getMethodCalls();

        $this->assertCount(0, $methodCalls);
        $this->pass->process($container);

        $methodCalls = $managerDef->getMethodCalls();
        $pkgSource = $this->rootDir.'/vendor/asset-asset/foobar/';
        $valid = array(
            realpath($pkgSource.'dist/js/foobar.js'),
            realpath($pkgSource.'dist/css/foobar.css'),
            realpath($pkgSource.'dist/css/foobar-theme.css'),
            realpath($pkgSource.'dist/fonts/font-family-regular.eot'),
            realpath($pkgSource.'dist/fonts/font-family-regular.svg'),
            realpath($pkgSource.'dist/fonts/font-family-regular.ttf'),
            realpath($pkgSource.'dist/fonts/font-family-regular.woff'),
            'assets/common.js',
        );

        $this->assertCount(count($valid), $methodCalls);

        foreach ($methodCalls as $methodCall) {
            $this->assertSame('addResource', $methodCall[0]);
            $methodDef = $methodCall[1][0];
            $this->assertInstanceOf('Symfony\Component\DependencyInjection\Definition', $methodDef);
            /* @var Definition $methodDef */
            $methodArgs = $methodDef->getArguments();
            $this->assertCount(5, $methodArgs);
            $output = is_array($methodArgs[1]) ? $methodArgs[2] : $methodArgs[1];
            $this->assertTrue(in_array($output, $valid));
        }
    }

    protected function includeAssetPackageDefinition(ContainerBuilder $container)
    {
        $packageManagerDef = $container->getDefinition('fxp_require_asset.assetic.config.package_manager');
        $package = array(
            'name' => 'acme_demo_package',
            'source_path' => realpath($this->rootDir.'/vendor/asset-asset/foobar'),
            'extensions' => array(
                'js',
                'css',
                'eot',
                'svg',
                'ttf',
                'woff',
            ),
            'patterns' => array(
                'dist/*',
                '!*.min.js',
                '!*.min.css',
            ),
        );

        $packageManagerDef->addMethodCall('addPackage', array($package));
    }

    protected function includeCommonAssetDefinition(ContainerBuilder $container)
    {
        $commons = array(
            'common_js' => array(
                'output'  => 'common.js',
                'filters' => array(),
                'options' => array(),
                'inputs'  => array(
                    '@foobar/js/component-a.js',
                    '@foobar/js/component-b.js',
                ),
            ),
        );

        $container->setParameter('fxp_require_asset.assetic.config.common_assets', $commons);
    }

    /**
     * Gets the container.
     *
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.cache_dir'   => $this->rootDir.'/cache',
            'kernel.debug'       => false,
            'kernel.environment' => 'test',
            'kernel.name'        => 'kernel',
            'kernel.root_dir'    => $this->rootDir,
            'kernel.charset'     => 'UTF-8',
            'assetic.debug'      => false,
            'kernel.bundles'     => array(),
            'locale'             => 'en',
        )));

        $extension = new FxpRequireAssetExtension();
        $container->registerExtension($extension);
        $extension->load(array(array()), $container);

        $asseticManager = new Definition('Assetic\Factory\LazyAssetManager');
        $container->setDefinition('assetic.asset_manager', $asseticManager);

        return $container;
    }

    protected function createFixtures()
    {
        foreach ($this->getFixtureFiles() as $filename) {
            $this->fs->dumpFile($this->rootDir.'/vendor/asset-asset/'.$filename, '');
        }
    }

    /**
     * @return array
     */
    protected function getFixtureFiles()
    {
        return array(
            'foobar/bower.json',
            'foobar/package.json',
            'foobar/README.md',
            'foobar/CONTRIBUTING.MD',
            'foobar/CNAME',
            'foobar/Gruntfile.js',
            'foobar/.travis.yml',
            'foobar/.gitignore',
            'foobar/.gitattributes',
            'foobar/dist/js/foobar.js',
            'foobar/dist/js/foobar.min.js',
            'foobar/dist/css/foobar.css',
            'foobar/dist/css/foobar.css.map',
            'foobar/dist/css/foobar.min.css',
            'foobar/dist/css/foobar-theme.css',
            'foobar/dist/css/foobar-theme.css.map',
            'foobar/dist/css/foobar-theme.min.css',
            'foobar/dist/fonts/font-family-regular.eot',
            'foobar/dist/fonts/font-family-regular.svg',
            'foobar/dist/fonts/font-family-regular.ttf',
            'foobar/dist/fonts/font-family-regular.woff',
            'foobar/doc/sitmap.xml',
            'foobar/doc/robot.txt',
            'foobar/doc/index.html',
            'foobar/fonts/font-family-regular.eot',
            'foobar/fonts/font-family-regular.svg',
            'foobar/fonts/font-family-regular.ttf',
            'foobar/fonts/font-family-regular.woff',
            'foobar/js/.jscsrc',
            'foobar/js/.jshintrc',
            'foobar/js/component-a.js',
            'foobar/js/component-b.js',
            'foobar/less/foobar.less',
            'foobar/less/foobar-theme.less',
            'foobar/less/variable.less',
            'foobar/less/mixins.less',
            'foobar/less/component-a.less',
            'foobar/less/component-b.less',
        );
    }
}
