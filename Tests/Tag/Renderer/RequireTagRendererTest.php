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
use Assetic\AssetManager;
use Fxp\Bundle\RequireAssetBundle\Tag\Renderer\RequireTagRenderer;
use Fxp\Component\RequireAsset\Tag\TagInterface;
use Symfony\Component\Templating\Asset\Package;
use Symfony\Component\Templating\Helper\CoreAssetsHelper;

/**
 * Require Tag Renderer Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RequireTagRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTargetPathWithTemplatingHelper()
    {
        $manager = new AssetManager();
        $packageDefault = new Package('v2');
        $helper = new CoreAssetsHelper($packageDefault);
        $renderer = new RequireTagRenderer($manager, $helper);

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
