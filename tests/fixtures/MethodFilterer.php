<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MethodFilterer
{
    public $lines = array();

    public function filter1($line)
    {
        $this->lines[] = $line;

        return $line;
    }

    public function filter2($line)
    {
        return str_replace(array(
            'if (true)',
            'function foo()',
        ), array(
            'if (false)',
            'function foo($arg)',
        ), $line);
    }
}
