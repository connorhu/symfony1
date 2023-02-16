<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MyWidgetForm extends sfWidgetForm
{
    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        return $this->renderTag('input', array_merge(array('name' => $name), $attributes)).$this->renderContentTag('textarea', null, array_merge(array('name' => $name), $attributes));
    }

    public function generateId($name, $value = null)
    {
        return parent::generateId($name, $value);
    }
}
