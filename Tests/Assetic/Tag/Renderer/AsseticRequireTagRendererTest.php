<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\Assetic\Tag\Renderer;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Fxp\Bundle\RequireAssetBundle\Assetic\Tag\Renderer\AsseticRequireTagRenderer;
use Fxp\Component\RequireAsset\Tag\TagInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;

/**
 * Assetic Require Tag Renderer Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AsseticRequireTagRendererTest extends TestCase
{
    public function testGetTargetPathWithTemplatingHelper()
    {
        $factory = new AssetFactory('web');
        $manager = new LazyAssetManager($factory);
        $versionStrategy = new StaticVersionStrategy('v2');
        $defaultPackage = new Package($versionStrategy);
        $packages = new Packages($defaultPackage);
        $renderer = new AsseticRequireTagRenderer($manager, $packages);

        $asset = $this->getMockBuilder('Assetic\Asset\AssetInterface')->getMock();
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

        $tag = $this->getMockBuilder('Fxp\Component\RequireAsset\Tag\RequireTagInterface')->getMock();
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
            ->method('getAssetName')
            ->will($this->returnValue('foo_bar_js'));
        $tag->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('foo/bar.js'));

        /* @var TagInterface $tag */
        $output = $renderer->render($tag);

        $this->assertSame('<script src="foo/bar.js?v2"></script>', $output);
    }
}
