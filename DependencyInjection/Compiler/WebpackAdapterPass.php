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

use Fxp\Component\RequireAsset\Exception\InvalidConfigurationException;
use Fxp\Component\RequireAsset\Webpack\Adapter\MockAdapter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Select the webpack plugin adapter.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class WebpackAdapterPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $adapter = $container->getParameter('fxp_require_asset.webpack.adapter');

        if ('auto' === $adapter) {
            $adapter = null !== $this->findManifestPath($container) ? 'manifest' : 'assets';
        }

        if ('test' === $container->getParameter('kernel.environment')) {
            $adapter = $this->configureMockAdapter($container);
        } elseif ('manifest' === $adapter) {
            $this->configureManifestAdapter($container);
        } elseif ('assets' === $adapter) {
            $this->configureAssetsAdapter($container);
        }

        $container->getParameterBag()->remove('fxp_require_asset.webpack.adapter');
        $container->setAlias('fxp_require_asset.webpack.adapter.default', 'fxp_require_asset.webpack.adapter.'.$adapter);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return string
     */
    private function configureMockAdapter(ContainerBuilder $container)
    {
        $adapter = 'mock';
        $container->setDefinition('fxp_require_asset.webpack.adapter.'.$adapter, new Definition(MockAdapter::class));

        $container->removeDefinition('fxp_require_asset.webpack.adapter.assets');
        $container->getParameterBag()->remove('fxp_require_asset.webpack.adapter.assets.file');
        $container->getParameterBag()->remove('fxp_require_asset.webpack.adapter.assets.cache_key');
        $container->removeAlias('fxp_require_asset.webpack.adapter.assets.cache');

        $container->removeDefinition('fxp_require_asset.webpack.adapter.manifest');
        $container->getParameterBag()->remove('fxp_require_asset.webpack.adapter.manifest.file');

        return $adapter;
    }

    /**
     * @param ContainerBuilder $container
     */
    private function configureManifestAdapter(ContainerBuilder $container): void
    {
        $manifestPath = $this->findManifestPath($container);

        if (null === $manifestPath) {
            throw new InvalidConfigurationException('The "fxp_require_asset.webpack.manifest_adapter.file" option or "framework.assets.json_manifest_path" is required to use the webpack manifest adapter');
        }

        $container->removeDefinition('fxp_require_asset.webpack.adapter.assets');
        $container->getParameterBag()->remove('fxp_require_asset.webpack.adapter.assets.file');
        $container->getParameterBag()->remove('fxp_require_asset.webpack.adapter.assets.cache_key');
        $container->removeAlias('fxp_require_asset.webpack.adapter.assets.cache');
    }

    /**
     * @param ContainerBuilder $container
     */
    private function configureAssetsAdapter(ContainerBuilder $container): void
    {
        $container->removeDefinition('fxp_require_asset.webpack.adapter.manifest');
        $container->getParameterBag()->remove('fxp_require_asset.webpack.adapter.manifest.file');
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return null|string
     */
    private function findManifestPath(ContainerBuilder $container)
    {
        $manifestPath = $container->getParameter('fxp_require_asset.webpack.adapter.manifest.file');

        if (null === $manifestPath && $container->hasDefinition('assets._version__default')) {
            $def = $container->getDefinition('assets._version__default');

            if (\count($def->getArguments()) > 0) {
                $manifestPath = $def->getArgument(0);
                $container->setParameter('fxp_require_asset.webpack.adapter.manifest.file', $manifestPath);
            }
        }

        return $manifestPath;
    }
}
