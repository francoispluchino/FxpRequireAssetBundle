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

use Symfony\Component\Config\Definition\Processor;
use Fxp\Bundle\RequireAssetBundle\DependencyInjection\Configuration;

/**
 * Tests case for Configuration.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration('ROOT_DIR', 'en'), array(array()));

        $this->assertEquals(
            array_merge(array(), self::getBundleDefaultConfig()),
            $config
        );
    }

    protected static function getBundleDefaultConfig()
    {
        return array(
            'output_prefix' => 'assets',
            'output_prefix_debug' => 'assets-dev',
            'composer_installed_path' => 'ROOT_DIR/../vendor/composer/installed.json',
            'native_npm' => true,
            'native_bower' => true,
            'base_dir' => 'ROOT_DIR/..',
            'default' => array(
                'replace_extensions' => false,
                'extensions' => array(),
                'patterns' => array(),
            ),
            'output_rewrites' => array(),
            'packages' => array(),
            'common_assets' => array(),
            'default_locale' => 'en',
            'fallback_locale' => null,
            'locales' => array(),
            'asset_replacement' => array(),
            'auto_configuration' => true,
            'less_assetic_filter' => 'less',
        );
    }
}
