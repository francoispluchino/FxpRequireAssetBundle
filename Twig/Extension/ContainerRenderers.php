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

use Fxp\Component\RequireAsset\Tag\Renderer\TagRendererInterface;

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
    protected $renderers = [];

    /**
     * Add template tag renderer.
     *
     * @param TagRendererInterface $renderer The template tag renderer
     *
     * @return self
     */
    public function addRenderer(TagRendererInterface $renderer)
    {
        $this->renderers[] = $renderer;

        return $this;
    }

    /**
     * Get the template tag renderers.
     *
     * @return TagRendererInterface[]
     */
    public function getRenderers()
    {
        return $this->renderers;
    }
}
