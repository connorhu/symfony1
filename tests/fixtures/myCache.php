<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myCache extends sfCache
{
    public function get($key, $default = null) {}

    public function has($key) {}

    public function set($key, $data, $lifetime = null) {}

    public function remove($key) {}

    public function clean($mode = sfCache::ALL) {}

    public function getTimeout($key) {}

    public function getLastModified($key) {}

    public function removePattern($pattern, $delimiter = ':') {}
}
