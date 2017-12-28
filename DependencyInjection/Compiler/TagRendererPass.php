<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Include the tag renderer into the twig extension.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TagRendererPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('twig.extension.fxp_require_asset')) {
            $definition = new Definition('Fxp\Bundle\RequireAssetBundle\Twig\Extension\ContainerRenderers');
            $definition->setPublic(true);

            foreach ($this->findAndSortTaggedServices('fxp_require_asset.tag_renderer', $container) as $service) {
                $definition->addMethodCall('addRenderer', [$service]);
            }

            $container->setDefinition('twig.extension.fxp_require_asset.container_tag_renderers', $definition);
        }
    }
}
