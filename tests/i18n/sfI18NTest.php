<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../PhpUnitSfTestHelperTrait.php';
require_once __DIR__.'/../fixtures/EnglishSentence.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfI18NTest extends Symfony1ApplicationTestCase
{
    use PhpUnitSfTestHelperTrait;

    public function getI18NGlobalDirs()
    {
        return array(__DIR__.'/../fixtures/messages');
    }

    public function getRootDir()
    {
        return sfConfig::get('sf_test_cache_dir', sys_get_temp_dir());
    }

    public function testTodoMigrate()
    {
        $this->resetSfConfig();

        $configuration = $this->getApplicationConfiguration();
        $dispatcher = $configuration->getEventDispatcher();
        $cache = new sfNoCache();

        // ->initialize()
        $this->diag('->initialize()');
        $i18n = new sfI18N($configuration, $cache);
        $dispatcher->notify(new sfEvent(null, 'user.change_culture', array('culture' => 'fr')));
        $this->is($i18n->getCulture(), 'fr', '->initialize() connects to the user.change_culture event');

        // passing a "culture" option to initialize() should set PHP locale
        if (version_compare(PHP_VERSION, '5.3', '<') && class_exists('Locale') && ($en = Locale::lookup(array('en-US'), 'en-US', true)) && ($fr = Locale::lookup(array('fr-FR'), 'fr-FR', true))) {
            $i18n = new sfI18N($configuration, $cache, array('culture' => $fr));
            $frLocale = localeconv();
            $i18n = new sfI18N($configuration, $cache, array('culture' => $en));
            $enLocale = localeconv();
            $this->isnt(serialize($frLocale), serialize($enLocale), '->initialize() sets the PHP locale when a "culture" option is provided');
        }

        // ->getCulture() ->setCulture()
        $this->diag('->getCulture() ->setCulture()');
        $i18n = new sfI18N($configuration, $cache);
        $this->is($i18n->getCulture(), 'en', '->getCulture() returns the current culture');
        $i18n->setCulture('fr');
        $this->is($i18n->getCulture(), 'fr', '->setCulture() sets the current culture');

        // ->__()
        $this->diag('->__()');
        sfConfig::set('sf_charset', 'UTF-8');
        $i18n = new sfI18N($configuration, $cache, array('culture' => 'fr'));
        $this->is($i18n->__('an english sentence'), 'une phrase en français', '->__() translates a string');
        $this->is($i18n->__(new EnglishSentence()), 'une phrase en français', '->__() translates an object with __toString()');
        $args = array('%timestamp%' => $timestamp = time());
        $this->is($i18n->__('Current timestamp is %timestamp%', $args), strtr('Le timestamp courant est %timestamp%', $args), '->__() takes an array of arguments as its second argument');
        $this->is($i18n->__('an english sentence', array(), 'messages_bis'), 'une phrase en français (bis)', '->__() takes a catalogue as its third argument');

        // test for #2161
        $this->is($i18n->__('1 minute'), '1 menit', '->__() "1 minute" translated as "1 menit"');
        $this->is($i18n->__('1'), '1', '->__() "1" translated as "1"');
        $this->is($i18n->__(1), '1', '->__() number 1 translated as "1"');

        $i18n->setCulture('fr_BE');
        $this->is($i18n->__('an english sentence'), 'une phrase en belge', '->__() translates a string');

        // debug
        $i18n = new sfI18N($configuration, $cache, array('debug' => true));
        $this->is($i18n->__('unknown'), '[T]unknown[/T]', '->__() adds a prefix and a suffix on untranslated strings if debug is on');
        $i18n = new sfI18N($configuration, $cache, array('debug' => true, 'untranslated_prefix' => '-', 'untranslated_suffix' => '#'));
        $this->is($i18n->__('unknown'), '-unknown#', '->initialize() can change the default prefix and suffix dor untranslated strings');

        // ->getCountry()
        $this->diag('->getCountry()');
        $i18n = new sfI18N($configuration, $cache, array('culture' => 'fr'));
        $this->is($i18n->getCountry('FR'), 'France', '->getCountry() returns the name of a country for the current culture');
        $this->is($i18n->getCountry('FR', 'es'), 'Francia', '->getCountry() takes an optional culture as its second argument');

        // ->getNativeName()
        $this->diag('->getNativeName()');
        $i18n = new sfI18N($configuration, $cache, array('culture' => 'fr'));
        $this->is($i18n->getNativeName('fr'), 'français', '->getNativeName() returns the name of a culture');

        // ->getTimestampForCulture()
        $this->diag('->getTimestampForCulture()');
        $i18n = new sfI18N($configuration, $cache, array('culture' => 'fr'));
        $this->is($i18n->getTimestampForCulture('15/10/2005'), mktime(0, 0, 0, '10', '15', '2005'), '->getTimestampForCulture() returns the timestamp for a data formatted in the current culture');
        $this->is($i18n->getTimestampForCulture('15/10/2005 15:33'), mktime(15, 33, 0, '10', '15', '2005'), '->getTimestampForCulture() returns the timestamp for a data formatted in the current culture');
        $this->is($i18n->getTimestampForCulture('10/15/2005', 'en_US'), mktime(0, 0, 0, '10', '15', '2005'), '->getTimestampForCulture() can take a culture as its second argument');
        $this->is($i18n->getTimestampForCulture('10/15/2005 3:33 pm', 'en_US'), mktime(15, 33, 0, '10', '15', '2005'), '->getTimestampForCulture() can take a culture as its second argument');
        $this->is($i18n->getTimestampForCulture('not a date'), null, '->getTimestampForCulture() returns the day, month and year for a data formatted in the current culture');

        // ->getDateForCulture()
        $this->diag('->getDateForCulture()');
        $i18n = new sfI18N($configuration, $cache, array('culture' => 'fr'));
        $this->is($i18n->getDateForCulture('15/10/2005'), array('15', '10', '2005'), '->getDateForCulture() returns the day, month and year for a data formatted in the current culture');
        $this->is($i18n->getDateForCulture('10/15/2005', 'en_US'), array('15', '10', '2005'), '->getDateForCulture() can take a culture as its second argument');
        $this->is($i18n->getDateForCulture(null), null, '->getDateForCulture() returns null in case of conversion problem');
        $this->is($i18n->getDateForCulture('not a date'), null, '->getDateForCulture() returns null in case of conversion problem');

        // german locale contains a dot as separator for date. See #7582
        $i18n = new sfI18N($configuration, $cache, array('culture' => 'de'));
        $this->is($i18n->getDateForCulture('15.10.2005'), array('15', '10', '2005'), '->getDateForCulture() returns the day, month and year for a data formatted in culture with dots as separators');
        $this->is($i18n->getDateForCulture('15x10x2005'), null, '->getDateForCulture() returns null in case of conversion problem with dots as separators');

        // ->getTimeForCulture()
        $this->diag('->getTimeForCulture()');
        $i18n = new sfI18N($configuration, $cache, array('culture' => 'fr'));
        $this->is($i18n->getTimeForCulture('15:33'), array('15', '33'), '->getTimeForCulture() returns the hour and minuter for a time formatted in the current culture');
        $this->is($i18n->getTimeForCulture('3:33 pm', 'en_US'), array(15, '33'), '->getTimeForCulture() can take a culture as its second argument');
        $this->is($i18n->getTimeForCulture(null), null, '->getTimeForCulture() returns null in case of conversion problem');
        $this->is($i18n->getTimeForCulture('0'), null, '->getTimeForCulture() returns null in case of conversion problem');
        $this->is($i18n->getTimeForCulture('not a time'), null, '->getTimeForCulture() returns null in case of conversion problem');

        // swedish locale contains a dot as separator for time. See #7582
        $i18n = new sfI18N($configuration, $cache, array('culture' => 'sv'));
        $this->is($i18n->getTimeForCulture('15.33'), array('15', '33'), '->getTimeForCulture() returns the hour and minuter for a time formatted in culture with dots as separators');
        $this->is($i18n->getTimeForCulture('15x33'), null, '->getTimeForCulture() returns null in case of conversion problem with dots as separators');
    }
}
