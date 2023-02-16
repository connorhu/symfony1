<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfAlwaysAbsoluteRoute extends sfRoute
{
    public function generate($params, $context = array(), $absolute = false)
    {
        $url = parent::generate($params, $context, $absolute);

        return 'http://'.$context['host'].$url;
    }
}
