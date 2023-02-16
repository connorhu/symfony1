<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myFilesystem extends sfFilesystem
{
    public function calculateRelativeDir($from, $to)
    {
        return parent::calculateRelativeDir($from, $to);
    }

    public function canonicalizePath($path)
    {
        return parent::canonicalizePath($path);
    }
}
