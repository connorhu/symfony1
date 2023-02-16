<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PostValidator extends sfValidatorBase
{
    protected function doClean($values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = "*{$value}*";
        }

        return $values;
    }
}
