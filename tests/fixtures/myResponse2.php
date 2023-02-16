<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myResponse2 extends sfResponse
{
    public function serialize() {}

    public function unserialize($serialized) {}

    public function __serialize() {}

    public function __unserialize($data) {}
}
