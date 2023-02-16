<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MyForm extends sfForm
{
    public function getStylesheets()
    {
        return array('/path/to/a/foo.css' => 'all', '/path/to/a/bar.css' => 'print');
    }

    public function getJavaScripts()
    {
        return array('/path/to/a/foo.js', '/path/to/a/bar.js');
    }
}
