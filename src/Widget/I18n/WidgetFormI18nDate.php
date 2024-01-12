<?php

namespace Symfony1\Components\Widget\I18n;

use Symfony1\Components\Widget\WidgetFormDate;
use Symfony1\Components\I18n\DateTimeFormatInfo;
use InvalidArgumentException;
use function array_combine;
use function range;
use function sprintf;
use function stripos;
use function strtr;
use function substr;
use function strripos;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfWidgetFormI18nDate represents a date widget.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class WidgetFormI18nDate extends WidgetFormDate
{
    /**
    * Constructor.
    *
    * Available options:
    *
    * * culture:      The culture to use for internationalized strings (required)
    month_format: The month format (name - default, short_name, number)
    *
    * @param array $options An array of options
    * @param array $attributes An array of default HTML attributes
    *
    * @see sfWidgetFormDate
    */
    protected function configure($options = array(), $attributes = array())
    {
        parent::configure($options, $attributes);
        $this->addRequiredOption('culture');
        $this->addOption('month_format');
        $culture = isset($options['culture']) ? $options['culture'] : 'en';
        $monthFormat = isset($options['month_format']) ? $options['month_format'] : 'name';
        // format
        $this->setOption('format', $this->getDateFormat($culture));
        // months
        $this->setOption('months', $this->getMonthFormat($culture, $monthFormat));
    }
    protected function getMonthFormat($culture, $monthFormat)
    {
        switch ($monthFormat) {
            case 'name':
                return array_combine(range(1, 12), DateTimeFormatInfo::getInstance($culture)->getMonthNames());
            case 'short_name':
                return array_combine(range(1, 12), DateTimeFormatInfo::getInstance($culture)->getAbbreviatedMonthNames());
            case 'number':
                return $this->getOption('months');
            default:
                throw new InvalidArgumentException(sprintf('The month format "%s" is invalid.', $monthFormat));
        }
    }
    protected function getDateFormat($culture)
    {
        $dateFormat = DateTimeFormatInfo::getInstance($culture)->getShortDatePattern();
        if (false === ($dayPos = stripos($dateFormat, 'd')) || false === ($monthPos = stripos($dateFormat, 'm')) || false === ($yearPos = stripos($dateFormat, 'y'))) {
            return $this->getOption('format');
        }
        return strtr($dateFormat, array(substr($dateFormat, $dayPos, strripos($dateFormat, 'd') - $dayPos + 1) => '%day%', substr($dateFormat, $monthPos, strripos($dateFormat, 'm') - $monthPos + 1) => '%month%', substr($dateFormat, $yearPos, strripos($dateFormat, 'y') - $yearPos + 1) => '%year%'));
    }
}
class_alias(WidgetFormI18nDate::class, 'sfWidgetFormI18nDate', false);