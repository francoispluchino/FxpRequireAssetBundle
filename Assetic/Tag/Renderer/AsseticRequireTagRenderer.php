<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Assetic\Tag\Renderer;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\LazyAssetManager;
use Fxp\Component\RequireAsset\Asset\Config\LocaleManagerInterface;
use Fxp\Component\RequireAsset\Assetic\Tag\Renderer\AsseticRequireTagRenderer as BaseAsseticRequireTagRenderer;
use Symfony\Component\Asset\Packages;

/**
 * Assetic require tag renderer with templating asset packages.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AsseticRequireTagRenderer extends BaseAsseticRequireTagRenderer
{
    /**
     * @var Packages
     */
    protected $packages;

    /**
     * Constructor.
     *
     * @param LazyAssetManager            $manager           The assetic manager
     * @param Packages                    $packages          The asset packages
     * @param LocaleManagerInterface|null $localeManager     The require locale asset manager
     * @param array                       $debugCommonAssets The common assets for debug mode without assetic common parts
     */
    public function __construct(LazyAssetManager $manager, Packages $packages,
                                LocaleManagerInterface $localeManager = null,
                                array $debugCommonAssets = [])
    {
        parent::__construct($manager, $localeManager, $debugCommonAssets);

        $this->packages = $packages;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTargetPath(AssetInterface $asseticAsset)
    {
        $target = parent::getTargetPath($asseticAsset);
        $target = $this->packages->getUrl($target);

        return $target;
    }
}
