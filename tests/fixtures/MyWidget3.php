<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MyWidget3 extends sfWidgetFormChoice
{
    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        return null;
    }

    public function getJavaScripts()
    {
        return array('/path/to/a/file.js');
    }

    public function getStylesheets()
    {
        return array('/path/to/a/file.css' => 'all');
    }
}
