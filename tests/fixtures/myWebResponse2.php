<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myWebResponse2 extends sfWebResponse
{
    public function getStatusText()
    {
        return $this->statusText;
    }

    public function normalizeHeaderName($name)
    {
        return parent::normalizeHeaderName($name);
    }
}
