<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PreValidator extends sfValidatorBase
{
    protected function doClean($values)
    {
        if (isset($values['s1'], $values['s2'])) {
            throw new sfValidatorError($this, 's1_or_s2', array('value' => $values));
        }

        return array_map('strtoupper', $values);
    }
}
