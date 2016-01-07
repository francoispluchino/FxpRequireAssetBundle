<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Listener;

use Fxp\Bundle\RequireAssetBundle\Twig\Extension\AssetExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listeners of kernel.exception for twig asset require.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TwigAssetSubscriber implements EventSubscriberInterface
{
    /**
     * @var AssetExtension
     */
    protected $assetExtension;

    /**
     * Constructor.
     *
     * @param AssetExtension $assetExtension The require asset extension
     */
    public function __construct(AssetExtension $assetExtension)
    {
        $this->assetExtension = $assetExtension;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array(
                array('resetTagPositionException', 10),
            ),
        );
    }

    /**
     * Reset the tag positions of require asset.
     */
    public function resetTagPositionException()
    {
        $this->assetExtension->resetTagPosition();
    }
}
