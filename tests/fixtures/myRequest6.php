<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myRequest6
{
    public $getParameters = array('page' => 5, 'sort' => 'asc');

    public function getHost()
    {
        return 'localhost';
    }

    public function getScriptName()
    {
        return 'index.php';
    }

    public function getHttpHeader($headerName)
    {
        return '/foo#|#/bar/';
    }

    public function getGetParameters()
    {
        return $this->getParameters;
    }
}
