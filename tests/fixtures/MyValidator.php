<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MyValidator extends sfValidatorDecorator
{
    public function getValidator()
    {
        return new sfValidatorString(array('min_length' => 2, 'trim' => true), array('required' => 'This string is required.'));
    }
}
