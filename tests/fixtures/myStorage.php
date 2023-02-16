<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myStorage extends sfStorage
{
    public function read($key) {}

    public function remove($key) {}

    public function shutdown() {}

    public function write($key, $data) {}

    public function regenerate($destroy = false) {}
}
