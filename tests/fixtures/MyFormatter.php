<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MyFormatter extends sfWidgetFormSchemaFormatter
{
    protected $rowFormat = "<li>\n  %error%%label%\n  %field%%help%\n%hidden_fields%</li>\n";
    protected $errorRowFormat = "<li>\n%errors%</li>\n";
    protected $decoratorFormat = "<ul>\n  %content%</ul>";

    public function unnestErrors($errors, $prefix = '')
    {
        return parent::unnestErrors($errors, $prefix);
    }

    public static function dropTranslationCallable()
    {
        self::$translationCallable = null;
    }
}
