<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Webpack\Tag\Renderer;

use Fxp\Component\RequireAsset\Asset\Config\LocaleManagerInterface;
use Fxp\Component\RequireAsset\Webpack\Tag\Renderer\WebpackRequireTagRenderer as BaseWebpackRequireTagRenderer;
use Fxp\Component\RequireAsset\Webpack\WebpackRequireAssetManager;
use Symfony\Component\Asset\Packages;

/**
 * Webpack require tag renderer with templating asset packages.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class WebpackRequireTagRenderer extends BaseWebpackRequireTagRenderer
{
    /**
     * @var Packages
     */
    protected $packages;

    /**
     * Constructor.
     *
     * @param WebpackRequireAssetManager  $manager       The webpack require asset manager
     * @param Packages                    $packages      The asset packages
     * @param null|LocaleManagerInterface $localeManager The require locale asset manager
     */
    public function __construct(
        WebpackRequireAssetManager $manager,
        Packages $packages,
        LocaleManagerInterface $localeManager = null
    ) {
        parent::__construct($manager, $localeManager);

        $this->packages = $packages;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAssetPath($assetName, $type)
    {
        return $this->packages->getUrl(parent::getAssetPath($assetName, $type));
    }
}
