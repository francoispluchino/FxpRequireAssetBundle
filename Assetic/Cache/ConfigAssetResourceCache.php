<?php

/*
 * This file is part of the Fxp Require Asset package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Assetic\Cache;

use Fxp\Component\RequireAsset\Assetic\Config\AssetResourceInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Require asset resources cache.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigAssetResourceCache
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var AssetResourceInterface[]|null
     */
    protected $cacheData;

    /**
     * Constructor.
     *
     * @param string $dir      The directory of cache
     * @param string $filename The filename of cache file
     */
    public function __construct($dir, $filename = 'config-asset-resources')
    {
        $this->filename = rtrim($dir, '/').DIRECTORY_SEPARATOR.$filename;
        $this->fs = new Filesystem();
    }

    /**
     * Checks if the cache has the config asset resources.
     *
     * @return bool
     */
    public function hasResources()
    {
        return null !== $this->cacheData || file_exists($this->filename);
    }

    /**
     * Sets the config asset resources.
     *
     * @param AssetResourceInterface[] $assets
     *
     * @return self
     */
    public function setResources(array $assets)
    {
        $this->cacheData = $assets;
        $this->fs->dumpFile($this->filename, serialize($assets));

        return $this;
    }

    /**
     * Gets the asset resources.
     *
     * @return AssetResourceInterface[]
     */
    public function getResources()
    {
        if (null !== $this->cacheData) {
            return $this->cacheData;
        }

        if ($this->hasResources()) {
            $this->cacheData = unserialize(file_get_contents($this->filename));
        } else {
            $this->setResources(array());
        }

        return $this->cacheData;
    }

    /**
     * Invalidate the cache.
     *
     * @return self
     */
    public function invalidate()
    {
        $this->cacheData = null;
        $this->fs->remove($this->filename);

        return $this;
    }
}
