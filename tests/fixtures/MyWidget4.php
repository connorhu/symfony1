<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MyWidget4 extends sfWidget
{
    protected function configure($options = array(), $attributes = array())
    {
        $this->addOption('foo');
    }

    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        return $this->attributesToHtml(array_merge($this->attributes, $attributes));
    }
}
