<?php

/*
 * This file is part of the Fxp Require Asset package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\HashableInterface;
use Fxp\Component\RequireAsset\Assetic\Filter\LessVariableFilter as BaseLessVariableFilter;
use Symfony\Component\DependencyInjection\Container;

/**
 * Add the variables of require asset package directories at the beginning of the less file.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class LessVariableFilter extends BaseLessVariableFilter implements HashableInterface
{
    /**
     * @var Container
     */
    public $container;

    /**
     * {@inheritdoc}
     */
    public function filterLoad(AssetInterface $asset)
    {
        $this->init();
        parent::filterLoad($asset);
    }

    /**
     * {@inheritdoc}
     */
    public function hash()
    {
        $this->init();

        return serialize($this);
    }

    /**
     * Init the filter.
     */
    protected function init()
    {
        if (null !== $this->container) {
            $this->packages = (array) $this->container->getParameter('fxp_require_asset.package_dirs');
            $this->variables = (array) $this->container->getParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables');
            $this->container = null;
        }
    }
}
