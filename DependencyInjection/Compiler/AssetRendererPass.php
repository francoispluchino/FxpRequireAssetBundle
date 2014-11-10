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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Include the twig asset renderer into the twig extension.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AssetRendererPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('twig.extension.fxp_require_asset')) {
            $definition = new Definition('Fxp\Bundle\RequireAssetBundle\Twig\Extension\ContainerRenderers');
            $definition->setPublic(true);

            foreach ($container->findTaggedServiceIds('fxp_require_asset.renderer') as $serviceId => $tag) {
                $definition->addMethodCall('addRenderer', array(new Reference($serviceId)));
            }

            $container->setDefinition('twig.extension.fxp_require_asset.container_renderers', $definition);
        }
    }
}
