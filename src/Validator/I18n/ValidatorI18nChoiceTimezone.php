<?php

namespace Symfony1\Components\Validator\I18n;

use Symfony1\Components\Validator\ValidatorChoice;
use Symfony1\Components\I18n\CultureInfo;
use function array_keys;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfValidatorI18nChoiceLanguage validates than the value is a valid timezone.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class ValidatorI18nChoiceTimezone extends ValidatorChoice
{
    /**
     * Configures the current validator.
     *
     * Available options:
     *
     * @param array $options An array of options
     * @param array $messages An array of error messages
     *
     * @see sfValidatorChoice
     */
    protected function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);
        $this->setOption('choices', array_keys(CultureInfo::getInstance()->getTimeZones()));
    }
}
class_alias(ValidatorI18nChoiceTimezone::class, 'sfValidatorI18nChoiceTimezone', false);