<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ValidatorIdentity extends sfValidatorBase
{
    protected function configure($options = array(), $messages = array())
    {
        $this->addOption('foo', 'bar');
        $this->addMessage('foo', 'bar');
    }

    public function testIsEmpty($value)
    {
        return $this->isEmpty($value);
    }

    protected function doClean($value)
    {
        return $value;
    }
}
