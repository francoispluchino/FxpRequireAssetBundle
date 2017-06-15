<?php

/*
 * This file is part of the Fxp Require Asset package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\Assetic\Cache;

use Fxp\Bundle\RequireAssetBundle\Assetic\Cache\ConfigAssetResourceCache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Config Asset Resource Cache Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigAssetResourceCacheTest extends TestCase
{
    /**
     * @var ConfigAssetResourceCache
     */
    protected $cache;

    protected function setUp()
    {
        $this->cache = new ConfigAssetResourceCache($this->getCacheDir(), $this->getCacheName());
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->getCacheDir());
    }

    protected function getCacheDir()
    {
        return sys_get_temp_dir().'/fxp_require_config-asset-resource-cache-test';
    }

    protected function getCacheName()
    {
        return 'config-asset-resources';
    }

    public function testBasic()
    {
        $this->assertFalse($this->cache->hasResources());
        $this->assertSame(array(), $this->cache->getResources());
        $this->assertTrue($this->cache->hasResources());

        $this->cache->invalidate();

        $this->assertFalse($this->cache->hasResources());
    }

    public function testCacheContent()
    {
        $this->assertFalse($this->cache->hasResources());

        $mb = $this
            ->getMockBuilder('Fxp\Component\RequireAsset\Assetic\Config\AssetResourceInterface')
            ->disableOriginalConstructor();
        $resources = array(
            $mb->getMock(),
            $mb->getMock(),
            $mb->getMock(),
        );

        $this->cache->setResources($resources);

        $this->assertTrue($this->cache->hasResources());
        $this->assertSame($resources, $this->cache->getResources());

        $cache = new ConfigAssetResourceCache($this->getCacheDir(), $this->getCacheName());

        $this->assertTrue($cache->hasResources());
        $this->assertEquals($resources, $cache->getResources());
    }
}
