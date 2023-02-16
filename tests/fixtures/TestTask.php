<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TestTask extends sfBaseTask
{
    protected function execute($arguments = array(), $options = array()) {}

    public function reloadAutoload()
    {
        parent::reloadAutoload();
    }

    public function initializeAutoload(sfProjectConfiguration $configuration, $reload = false)
    {
        parent::initializeAutoload($configuration, $reload);
    }
}
