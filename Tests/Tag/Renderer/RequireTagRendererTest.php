<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\Tag\Renderer;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Fxp\Bundle\RequireAssetBundle\Tag\Renderer\RequireTagRenderer;
use Fxp\Component\RequireAsset\Tag\TagInterface;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;

/**
 * Require Tag Renderer Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RequireTagRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTargetPathWithTemplatingHelper()
    {
        $factory = new AssetFactory('web');
        $manager = new LazyAssetManager($factory);
        $versionStrategy = new StaticVersionStrategy('v2');
        $defaultPackage = new Package($versionStrategy);
        $packages = new Packages($defaultPackage);
        $renderer = new RequireTagRenderer($manager, $packages);

        $asset = $this->getMock('Assetic\Asset\AssetInterface');
        $asset->expects($this->any())
            ->method('getTargetPath')
            ->will($this->returnValue('foo/bar.js'));
        $asset->expects($this->any())
            ->method('getVars')
            ->will($this->returnValue(array()));
        $asset->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue(array()));
        /* @var AssetInterface $asset */
        $manager->set('foo_bar_js', $asset);

        $tag = $this->getMock('Fxp\Component\RequireAsset\Tag\RequireTagInterface');
        $tag->expects($this->any())
            ->method('getInputs')
            ->will($this->returnValue(array()));
        $tag->expects($this->any())
            ->method('getHtmlTag')
            ->will($this->returnValue('script'));
        $tag->expects($this->any())
            ->method('getLinkAttribute')
            ->will($this->returnValue('src'));
        $tag->expects($this->any())
            ->method('shortEndTag')
            ->will($this->returnValue(false));
        $tag->expects($this->any())
            ->method('getAsseticName')
            ->will($this->returnValue('foo_bar_js'));
        $tag->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('foo/bar.js'));

        /* @var TagInterface $tag */
        $output = $renderer->render($tag);

        $this->assertSame('<script src="foo/bar.js?v2"></script>', $output);
    }
}
