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
 * sfValidatorI18nChoiceCountry validates than the value is a valid country.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class ValidatorI18nChoiceCountry extends ValidatorChoice
{
    /**
     * Configures the current validator.
     *
     * Available options:
     *
     * * countries: An array of country codes to use (ISO 3166)
     *
     * @param array $options An array of options
     * @param array $messages An array of error messages
     *
     * @see sfValidatorChoice
     */
    protected function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);
        $this->addOption('countries');
        // populate choices with all countries
        $countries = array_keys(CultureInfo::getInstance()->getCountries());
        // restrict countries to a sub-set
        if (isset($options['countries'])) {
            if ($problems = array_diff($options['countries'], $countries)) {
                throw new InvalidArgumentException(sprintf('The following countries do not exist: %s.', implode(', ', $problems)));
            }
            $countries = $options['countries'];
        }
        $this->setOption('choices', $countries);
    }
}
class_alias(ValidatorI18nChoiceCountry::class, 'sfValidatorI18nChoiceCountry', false);