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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Override the config by the global custom config.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        /* @var ParameterBag $pb */
        $pb = $container->getParameterBag();

        $this->configureManager($container, $pb, 'file_extension_manager', 'file_extensions', 'addDefaultExtensions');
        $this->configureManager($container, $pb, 'pattern_manager', 'patterns', 'addDefaultPatterns');
        $this->configureManager($container, $pb, 'output_manager', 'output_rewrites', 'addOutputPatterns');
        $this->configureManager($container, $pb, 'package_manager', 'packages', 'addPackages');
        $this->configureReplacement($container);
    }

    /**
     * Configure the asset package section.
     *
     * @param ContainerBuilder $container
     * @param ParameterBag     $pb
     * @param string           $idManager
     * @param string           $idParameters
     * @param string           $methodCall
     */
    protected function configureManager(ContainerBuilder $container, ParameterBag $pb, $idManager, $idParameters, $methodCall)
    {
        $def = $container->getDefinition('fxp_require_asset.assetic.config.'.$idManager);
        $packages = $container->getParameter('fxp_require_asset.assetic.config.'.$idParameters);

        $def->addMethodCall($methodCall, array($packages));
        $pb->remove('fxp_require_asset.assetic.config.'.$idParameters);
    }

    /**
     * Configure the asset replacement.
     *
     * @param ContainerBuilder $container
     */
    protected function configureReplacement(ContainerBuilder $container)
    {
        $def = $container->getDefinition('fxp_require_asset.assetic.config.asset_replacement_manager');
        $replacement = $container->getParameter('fxp_require_asset.assetic.config.asset_replacement');
        $def->addMethodCall('addReplacements', array($replacement));
    }
}
