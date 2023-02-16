<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfSimpleCache extends sfCache
{
    public $data = array();

    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function set($key, $data, $lifetime = null)
    {
        $this->data[$key] = $data;
    }

    public function remove($key)
    {
        unset($this->data[$key]);
    }

    public function removePattern($pattern, $delimiter = ':')
    {
        $this->data = array();
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function clean($mode = sfCache::ALL)
    {
        $this->data = array();
    }

    public function getLastModified($key)
    {
        return 0;
    }

    public function getTimeout($key)
    {
        return 0;
    }
}
