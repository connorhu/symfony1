<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myLogger extends sfLogger
{
    public $log = '';

    protected function doLog($message, $priority)
    {
        $this->log .= $message;
    }
}
