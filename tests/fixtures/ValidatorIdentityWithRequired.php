<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ValidatorIdentityWithRequired extends sfValidatorBase
{
    protected function configure($options = array(), $messages = array())
    {
        $this->addRequiredOption('foo');
    }

    protected function doClean($value)
    {
        return $value;
    }
}
