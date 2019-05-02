<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\Listener;

use Fxp\Bundle\RequireAssetBundle\Listener\TwigAssetSubscriber;
use Fxp\Bundle\RequireAssetBundle\Twig\Extension\AssetExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tests for twig asset subscriber.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class TwigAssetSubscriberTest extends TestCase
{
    public function testResetTagPositionException(): void
    {
        /** @var AssetExtension|\PHPUnit_Framework_MockObject_MockObject $ext */
        $ext = $this->getMockBuilder(AssetExtension::class)->disableOriginalConstructor()->getMock();
        $ext->expects($this->once())
            ->method('resetTagPosition')
        ;

        $subscriber = new TwigAssetSubscriber($ext);
        $validEvents = [
            KernelEvents::EXCEPTION,
        ];

        $this->assertSame($validEvents, array_keys($subscriber->getSubscribedEvents()));

        $subscriber->resetTagPositionException();
    }
}
