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

use Fxp\Component\RequireAsset\Twig\Extension\RequireAssetExtension as BaseRequireAssetExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Require asset extension.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RequireAssetExtension extends BaseRequireAssetExtension
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * {@inheritdoc}
     */
    public function requireAsset($asset)
    {
        if (null !== $this->container) {
            $managerId = 'fxp_require_asset.chain_require_asset_manager';
            $this->manager = $this->container->get($managerId);
            $this->container = null;
        }

        return parent::requireAsset($asset);
    }
}
