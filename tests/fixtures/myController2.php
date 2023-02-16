<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myController2
{
    public function genUrl($parameters = array(), $absolute = false)
    {
        return ($absolute ? '/' : '').$parameters;
    }
}
