<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MySessionStorage extends sfSessionTestStorage
{
    public function regenerate($destroy = false)
    {
        $this->sessionId = rand(1, 9999);

        return true;
    }
}
