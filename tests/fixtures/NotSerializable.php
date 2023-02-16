<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// we test with non serializable objects
// to ensure that the errors are always serializable
// even if you use PDO as a session handler
class NotSerializable implements Serializable
{
    public function serialize()
    {
        throw new Exception('Not serializable');
    }

    public function unserialize($serialized)
    {
        throw new Exception('Not serializable');
    }

    public function __serialize()
    {
        throw new Exception('Not serializable');
    }

    public function __unserialize($data)
    {
        throw new Exception('Not serializable');
    }
}
