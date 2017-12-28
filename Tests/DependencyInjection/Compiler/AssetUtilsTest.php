<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\DependencyInjection\Compiler;

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler\AssetUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Asset utils UTests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AssetUtilsTest extends TestCase
{
    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var Filesystem
     */
    protected $fs;

    protected function setUp()
    {
        $this->projectDir = sys_get_temp_dir().'/require_asset_asset_utils_tests';
        $this->fs = new Filesystem();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->projectDir);
    }

    public function testLoadJsonFile()
    {
        $content = [
            'foo' => 'Bar',
            'bar' => 'Foo',
        ];

        $this->fs->dumpFile($this->projectDir.'/foo.json', json_encode($content));
        $json = AssetUtils::loadJsonFile($this->projectDir.'/foo.json');

        $this->assertEquals($content, $json);
    }

    public function testLoadJsonFileWithNonExistentFile()
    {
        $json = AssetUtils::loadJsonFile($this->projectDir.'/foo.json');

        $this->assertEquals([], $json);
    }

    public function testLoadJsonFileWithInvalidContent()
    {
        $this->fs->dumpFile($this->projectDir.'/foo.json', json_encode('INVALID'));
        $json = AssetUtils::loadJsonFile($this->projectDir.'/foo.json');

        $this->assertEquals([], $json);
    }
}
