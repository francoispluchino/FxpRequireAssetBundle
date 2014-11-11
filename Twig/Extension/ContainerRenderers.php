<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Twig\Extension;

use Fxp\Component\RequireAsset\Twig\Renderer\AssetRendererInterface;

/**
 * Container Renderers for twig extension (to avoid the circular reference).
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ContainerRenderers
{
    /**
     * @var array
     */
    protected $renderers = array();

    /**
     * Add twig asset renderer.
     *
     * @param AssetRendererInterface $renderer The renderer
     *
     * @return self
     */
    public function addRenderer(AssetRendererInterface $renderer)
    {
        $this->renderers[] = $renderer;

        return $this;
    }

    /**
     * Get the twig asset renderers.
     *
     * @return AssetRendererInterface[]
     */
    public function getRenderers()
    {
        return $this->renderers;
    }
}
