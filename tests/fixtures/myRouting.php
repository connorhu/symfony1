<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myRouting extends sfPatternRouting
{
    public $currentInternalUri = 'currentModule/currentAction?currentKey=currentValue';

    public function getCurrentInternalUri($with_route_name = false)
    {
        return $this->currentInternalUri;
    }
}
