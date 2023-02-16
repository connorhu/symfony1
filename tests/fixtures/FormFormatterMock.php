<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class FormFormatterMock extends sfWidgetFormSchemaFormatter
{
    public $translateSubjects = array();

    public function __construct() {}

    public function translate($subject, $parameters = array())
    {
        $this->translateSubjects[] = $subject;

        return sprintf('translation[%s]', $subject);
    }
}
