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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Override the config by the global custom config.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TwigCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $twigExtension = $container->getDefinition('twig.extension.fxp_require_asset');

        foreach ($container->findTaggedServiceIds('fxp_require_asset.require_tag') as $id => $attrs) {
            $twigExtension->addMethodCall('addTag', array(new Reference($id)));
        }
    }
}
