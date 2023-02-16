<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class WidgetFormStub extends sfWidget
{
    public function __construct() {}

    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        return sprintf('##%s##', __CLASS__);
    }
}
