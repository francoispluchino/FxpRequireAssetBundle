<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\Webpack\Tag\Renderer;

use Fxp\Bundle\RequireAssetBundle\Webpack\Tag\Renderer\WebpackRequireTagRenderer;
use Fxp\Component\RequireAsset\Tag\RequireTagInterface;
use Fxp\Component\RequireAsset\Tag\TagInterface;
use Fxp\Component\RequireAsset\Webpack\WebpackRequireAssetManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;

/**
 * Webpack Require Tag Renderer Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class WebpackRequireTagRendererTest extends TestCase
{
    public function testGetTargetPathWithTemplatingHelper(): void
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|WebpackRequireAssetManager $manager */
        $manager = $this->getMockBuilder(WebpackRequireAssetManager::class)->disableOriginalConstructor()->getMock();
        $versionStrategy = new StaticVersionStrategy('v2');
        $defaultPackage = new Package($versionStrategy);
        $packages = new Packages($defaultPackage);
        $renderer = new WebpackRequireTagRenderer($manager, $packages);

        $asset = '@webpack/foo_bar_js';
        $assetPath = '/assets/foo_bar_js.js';

        $manager->expects($this->any())
            ->method('has')
            ->with($asset)
            ->willReturn(true)
        ;

        $manager->expects($this->any())
            ->method('getPath')
            ->with($asset)
            ->willReturn($assetPath)
        ;

        $tag = $this->getMockBuilder(RequireTagInterface::class)->getMock();
        $tag->expects($this->any())
            ->method('getHtmlTag')
            ->will($this->returnValue('script'))
        ;
        $tag->expects($this->any())
            ->method('getLinkAttribute')
            ->will($this->returnValue('src'))
        ;
        $tag->expects($this->any())
            ->method('shortEndTag')
            ->will($this->returnValue(false))
        ;
        $tag->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($asset))
        ;

        /** @var TagInterface $tag */
        $output = $renderer->render($tag);

        $this->assertSame('<script src="/assets/foo_bar_js.js?v2"></script>', $output);
    }
}
