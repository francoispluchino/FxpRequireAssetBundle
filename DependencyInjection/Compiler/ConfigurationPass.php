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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Override the config by the global custom config.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->getParameter('fxp_require_asset.config.auto_configuration')) {
            $this->processDefault($container);
        }

        /* @var ParameterBag $pb */
        $pb = $container->getParameterBag();
        $pb->remove('fxp_require_asset.config.auto_configuration');
        $pb->remove('fxp_require_asset.assetic.config.less_filter');
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function processDefault(ContainerBuilder $container)
    {
        $lessFilter = $container->getParameter('fxp_require_asset.assetic.config.less_filter');
        $extManagerDef = $container->getDefinition('fxp_require_asset.assetic.config.file_extension_manager');
        $configs = array(
            'css' => array(
                'filters' => array('requirecssrewrite'),
            ),
            'less' => array(
                'filters' => array('lessvariable', 'parameterbag', $lessFilter, 'requirecssrewrite'),
                'extension' => 'css',
            ),
        );

        $extManagerDef->addMethodCall('addDefaultExtensions', array($configs));
    }
}
