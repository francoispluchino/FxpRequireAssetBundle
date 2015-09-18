<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tag\Renderer;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\LazyAssetManager;
use Fxp\Component\RequireAsset\Assetic\Config\LocaleManagerInterface;
use Fxp\Component\RequireAsset\Tag\Renderer\RequireTagRenderer as BaseRequireTagRenderer;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

/**
 * Require tag renderer with templating asset helper.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RequireTagRenderer extends BaseRequireTagRenderer
{
    /**
     * @var AssetsHelper
     */
    protected $helper;

    /**
     * Constructor.
     *
     * @param LazyAssetManager            $manager           The assetic manager
     * @param AssetsHelper                $helper            The assets helper
     * @param LocaleManagerInterface|null $localeManager     The require locale asset manager
     * @param array                       $debugCommonAssets The common assets for debug mode without assetic common parts
     */
    public function __construct(LazyAssetManager $manager, AssetsHelper $helper,
                                LocaleManagerInterface $localeManager = null,
                                array $debugCommonAssets = array())
    {
        parent::__construct($manager, $localeManager, $debugCommonAssets);

        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTargetPath(AssetInterface $asseticAsset)
    {
        $target = parent::getTargetPath($asseticAsset);
        $target = $this->helper->getUrl($target);

        return $target;
    }
}
