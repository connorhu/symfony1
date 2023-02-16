<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function testFunctionCache($arg1, $arg2)
{
    static $counter = 0;

    ++$counter;

    return $arg1.$arg2.$counter;
}
