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

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\ConfigurationPass;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\TagRendererPass;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\BundleAssetsPass;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\CompilerAssetsPass;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\ComposerAssetsPass;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\ConfigurationCompilerPass;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\TwigCompilerPass;
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
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TagRendererPass());
        $container->addCompilerPass(new ComposerAssetsPass());
        $container->addCompilerPass(new BundleAssetsPass());
        $container->addCompilerPass(new ConfigurationPass());
        $container->addCompilerPass(new TwigCompilerPass());
        $container->addCompilerPass(new ConfigurationCompilerPass(), PassConfig::TYPE_OPTIMIZE);
        $container->addCompilerPass(new CompilerAssetsPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
