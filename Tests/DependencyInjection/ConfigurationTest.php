<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Tests\DependencyInjection;

use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Tests case for Configuration.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration('PROJECT_DIR', 'en'), [[]]);

        $this->assertEquals(
            array_merge([], self::getBundleDefaultConfig()),
            $config
        );
    }

    protected static function getBundleDefaultConfig()
    {
        return [
            'output_prefix' => 'assets',
            'output_prefix_debug' => 'assets-dev',
            'composer_installed_path' => 'PROJECT_DIR/vendor/composer/installed.json',
            'native_npm' => true,
            'native_bower' => true,
            'base_dir' => 'PROJECT_DIR',
            'default' => [
                'replace_extensions' => false,
                'extensions' => [],
                'patterns' => [],
            ],
            'output_rewrites' => [],
            'packages' => [],
            'common_assets' => [],
            'default_locale' => 'en',
            'fallback_locale' => null,
            'locales' => [],
            'asset_replacement' => [],
            'auto_configuration' => true,
            'less_assetic_filter' => 'less',
            'assetic' => true,
            'twig' => true,
            'webpack' => [
                'enabled' => true,
                'adapter' => 'auto',
                'manifest_adapter' => [
                    'file' => null,
                ],
                'assets_adapter' => [
                    'file' => 'PROJECT_DIR/assets.json',
                    'cache' => [
                        'enabled' => null,
                        'key' => 'fxp_require_asset_webpack_assets',
                        'service_id' => 'cache.app',
                    ],
                ],
            ],
        ];
    }
}
