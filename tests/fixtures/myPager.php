<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myPager extends sfPager
{
    public function init() {}

    public function retrieveObject($offset) {}

    public function getResults()
    {
        $this->setNbResults(2);

        return array('foo', 'bar');
    }
}
