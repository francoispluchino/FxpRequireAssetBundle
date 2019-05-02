<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle;

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\RequireAssetManagerPass;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\TagRendererPass;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\TwigCompilerPass;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\WebpackAdapterPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpRequireAssetBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RequireAssetManagerPass());
        $container->addCompilerPass(new TagRendererPass());
        $container->addCompilerPass(new TwigCompilerPass());
        $container->addCompilerPass(new WebpackAdapterPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -200);
    }
}
