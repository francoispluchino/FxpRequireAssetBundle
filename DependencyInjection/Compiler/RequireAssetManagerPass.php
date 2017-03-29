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

/**
 * Include the require asset manager into the chain require asset manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RequireAssetManagerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('fxp_require_asset.chain_require_asset_manager')) {
            $definition = $container->getDefinition('fxp_require_asset.chain_require_asset_manager');

            foreach ($this->findAndSortTaggedServices('fxp_require_asset.require_asset_manager', $container) as $service) {
                $definition->addMethodCall('addRequireAssetManager', array($service));
            }
        }
    }
}
