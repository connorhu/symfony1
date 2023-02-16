<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myController3
{
    public function genUrl($parameters = array(), $absolute = false)
    {
        $url = is_array($parameters) && isset($parameters['sf_route']) ? $parameters['sf_route'] : 'module/action';

        return ($absolute ? '/' : '').$url;
    }
}
