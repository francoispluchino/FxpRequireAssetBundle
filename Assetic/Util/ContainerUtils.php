<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\Assetic\Util;

use Assetic\Util\CssUtils;

/**
 * Container Service Utils.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class ContainerUtils
{
    const REGEX_PARAMETER_BAG = '/%([A-Za-z0-9._\-]+)%/';

    /**
     * Filters all CSS url()'s through a callable.
     *
     * @param string   $content  The CSS
     * @param callable $callback A PHP callable
     *
     * @return string The filtered CSS
     */
    public static function filterParameters($content, $callback)
    {
        $pattern = static::REGEX_PARAMETER_BAG;

        return CssUtils::filterCommentless($content, function ($part) use (& $callback, $pattern) {
            return preg_replace_callback($pattern, $callback, $part);
        });
    }
}
