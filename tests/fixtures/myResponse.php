<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myResponse extends sfWebResponse
{
    public function resetAssets()
    {
        $this->javascripts = array_combine($this->positions, array_fill(0, count($this->positions), array()));
        $this->stylesheets = array_combine($this->positions, array_fill(0, count($this->positions), array()));
    }
}
