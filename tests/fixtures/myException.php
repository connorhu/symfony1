<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myException extends sfException
{
    public static function formatArgsTest($args, $single = false, $format = 'html')
    {
        return parent::formatArgs($args, $single, $format);
    }
}
