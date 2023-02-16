<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class testFunctionCache
{
    public static $count = 0;

    public static function testStatic($arg1, $arg2)
    {
        ++self::$count;

        return $arg1.$arg2;
    }

    public function test($arg1, $arg2)
    {
        ++self::$count;

        return $arg1.$arg2;
    }
}
