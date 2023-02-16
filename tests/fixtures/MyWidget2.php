<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MyWidget2 extends sfWidgetForm
{
    protected function configure($options = array(), $attributes = array())
    {
        $this->addRequiredOption('name');
    }

    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        return null;
    }

    public function getJavaScripts()
    {
        return array('/path/to/a/'.$this->getOption('name').'.js', '/path/to/foo.js');
    }

    public function getStylesheets()
    {
        return array('/path/to/a/'.$this->getOption('name').'.css' => 'all', '/path/to/foo.css' => 'all');
    }
}
