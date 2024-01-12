<?php

namespace Symfony1\Components\Validator\I18n;

use Symfony1\Components\Validator\ValidatorChoice;
use Symfony1\Components\I18n\CultureInfo;
use InvalidArgumentException;
use function array_keys;
use function array_diff;
use function sprintf;
use function implode;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfValidatorI18nChoiceLanguage validates than the value is a valid language.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class ValidatorI18nChoiceLanguage extends ValidatorChoice
{
    /**
     * Configures the current validator.
     *
     * Available options:
     *
     * * languages: An array of language codes to use
     *
     * @param array $options An array of options
     * @param array $messages An array of error messages
     *
     * @see sfValidatorChoice
     */
    protected function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);
        $this->addOption('languages');
        // populate choices with all languages
        $languages = array_keys(CultureInfo::getInstance()->getLanguages());
        // restrict languages to a sub-set
        if (isset($options['languages'])) {
            if ($problems = array_diff($options['languages'], $languages)) {
                throw new InvalidArgumentException(sprintf('The following languages do not exist: %s.', implode(', ', $problems)));
            }
            $languages = $options['languages'];
        }
        $this->setOption('choices', $languages);
    }
}
class_alias(ValidatorI18nChoiceLanguage::class, 'sfValidatorI18nChoiceLanguage', false);