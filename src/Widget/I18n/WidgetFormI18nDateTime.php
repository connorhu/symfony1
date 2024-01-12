<?php

namespace Symfony1\Components\Widget\I18n;

use Symfony1\Components\Widget\WidgetFormDateTime;
use Symfony1\Components\I18n\DateTimeFormatInfo;
use function str_replace;
use function array_merge;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfWidgetFormI18nDateTime represents a date and time widget.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class WidgetFormI18nDateTime extends WidgetFormDateTime
{
    /**
     * Constructor.
     *
     * Available options:
     *
     * * culture: The culture to use for internationalized strings (required)
     *
     * @param array $options An array of options
     * @param array $attributes An array of default HTML attributes
     *
     * @see sfWidgetFormDateTime
     */
    protected function configure($options = array(), $attributes = array())
    {
        parent::configure($options, $attributes);
        $this->addRequiredOption('culture');
        $culture = isset($options['culture']) ? $options['culture'] : 'en';
        // format
        $this->setOption('format', str_replace(array('{0}', '{1}'), array('%time%', '%date%'), DateTimeFormatInfo::getInstance($culture)->getDateTimeOrderPattern()));
    }
    /**
     * @see sfWidgetFormDateTime
     */
    protected function getDateWidget($attributes = array())
    {
        return new WidgetFormI18nDate(array_merge(array('culture' => $this->getOption('culture')), $this->getOptionsFor('date')), $this->getAttributesFor('date', $attributes));
    }
    /**
     * @see sfWidgetFormDateTime
     */
    protected function getTimeWidget($attributes = array())
    {
        return new WidgetFormI18nTime(array_merge(array('culture' => $this->getOption('culture')), $this->getOptionsFor('time')), $this->getAttributesFor('time', $attributes));
    }
}
class_alias(WidgetFormI18nDateTime::class, 'sfWidgetFormI18nDateTime', false);