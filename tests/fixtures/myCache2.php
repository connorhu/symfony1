<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myCache2 extends sfCache
{
    public static $cache = array();

    public function initialize($parameters = array()) {}

    public function get($key, $default = null)
    {
        return isset(self::$cache[$key]) ? self::$cache[$key] : $default;
    }

    public function has($key)
    {
        return isset(self::$cache[$key]);
    }

    public function set($key, $data, $lifetime = null)
    {
        self::$cache[$key] = $data;
    }

    public function remove($key)
    {
        unset(self::$cache[$key]);
    }

    public function removePattern($pattern, $delimiter = ':')
    {
        $pattern = '#^'.str_replace('*', '.*', $pattern).'$#';
        foreach (self::$cache as $key => $value) {
            if (preg_match($pattern, $key)) {
                unset(self::$cache[$key]);
            }
        }
    }

    public function clean($mode = sfCache::ALL)
    {
        self::$cache = array();
    }

    public function getTimeout($key)
    {
        return time() - 60;
    }

    public function getLastModified($key)
    {
        return time() - 600;
    }

    public static function clear()
    {
        self::$cache = array();
    }
}
