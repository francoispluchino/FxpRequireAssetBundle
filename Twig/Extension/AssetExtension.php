<?php

/*
 * This file is part of the Fxp Require Asset package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Twig\Extension;

use Fxp\Component\RequireAsset\Twig\Extension\AssetExtension as BaseAssetExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Asset extension.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AssetExtension extends BaseAssetExtension
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * {@inheritdoc}
     */
    public function renderAssets()
    {
        if (null !== $this->container) {
            $id = 'twig.extension.fxp_require_asset.container_renderers';
            $this->setRenderers($this->container->get($id)->getRenderers());
            $this->container = null;
        }

        parent::renderAssets();
    }
}
