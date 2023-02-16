<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../PhpUnitSfTestHelperTrait.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfCultureInfoTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->getInstance()
        $this->diag('->getInstance()');
        $c = sfCultureInfo::getInstance();
        $this->is($c->getName(), 'en', '->__construct() returns an object with "en" as the default culture');
        $c = sfCultureInfo::getInstance('fr');
        $this->is($c->getName(), 'fr', '->__construct() takes a culture as its first argument');
        $c = sfCultureInfo::getInstance('');
        $this->is($c->getName(), 'en', '->__construct() returns an object with "en" as the default culture');

        // __toString()
        $this->diag('__toString()');
        $c = sfCultureInfo::getInstance();
        $this->is($c->__toString(), 'en', '->__toString() returns the name of the culture');

        try {
            $c = sfCultureInfo::getInstance('xxx');
            $this->fail('->__construct() throws an exception if the culture is not valid');
        } catch (sfException $e) {
            $this->pass('->__construct() throws an exception if the culture is not valid');
        }

        $c_en = sfCultureInfo::getInstance();
        $c_fr = sfCultureInfo::getInstance('fr');

        // ->getLanguage()
        $this->diag('->getLanguage()');
        $language_en = $c_en->getLanguage('fr');
        $language_fr = $c_fr->getLanguage('fr');
        $this->is($language_en, 'French', '->getLanguage() returns the language name for the current culture');
        $this->is($language_fr, 'français', '->getLanguage() returns the language name for the current culture');

        try {
            $c_en->getLanguage('gb');
            $this->fail('->getLanguage() throws an Exception if the given language is invalid.');
        } catch (Exception $e) {
            $this->pass('->getLanguage() throws an Exception if the given language is invalid.');
        }

        // ->getCurrency()
        $this->diag('->getCurrency()');
        $currency_en = $c_en->getCurrency('EUR');
        $currency_fr = $c_fr->getCurrency('EUR');
        $this->is($currency_en, 'Euro', '->getCurrency() returns the currency name for the current culture');
        $this->is($currency_fr, 'euro', '->getCurrency() returns the currency name for the current culture');

        try {
            $c_en->getCurrency('FRANCS');
            $this->fail('->getCurrency() throws an Exception if the given currency is invalid.');
        } catch (Exception $e) {
            $this->pass('->getCurrency() throws an Exception if the given currency is invalid.');
        }

        // ->getCountry()
        $this->diag('->getCountry()');
        $country_en = $c_en->getCountry('FR');
        $country_fr = $c_fr->getCountry('FR');
        $this->is($country_en, 'France', '->getCountry() returns the country name for the current culture');
        $this->is($country_fr, 'France', '->getCountry() returns the country name for the current culture');

        try {
            $c_en->getCountry('en');
            $this->fail('->getCountry() throws an Exception if the given country is invalid.');
        } catch (Exception $e) {
            $this->pass('->getCountry() throws an Exception if the given country is invalid.');
        }

        // ->getLanguages()
        $this->diag('->getLanguages()');
        $languages_en = $c_en->getLanguages();
        $languages_fr = $c_fr->getLanguages();
        $this->is($languages_en['fr'], 'French', '->getLanguages() returns a list of languages in the language of the localized version');
        $this->is($languages_fr['fr'], 'français', '->getLanguages() returns a list of languages in the language of the localized version');
        $this->is($languages_en, $c_en->Languages, '->getLanguages() is equivalent to ->Languages');

        $languages = $c_en->getLanguages(array('fr', 'es'));
        $this->is(array_keys($languages), array('fr', 'es'), '->getLanguages() takes an array of languages as its first argument');

        try {
            $c_en->getLanguages(array('fr', 'gb'));
            $this->fail('->getLanguages() throws an Exception if the list of given languages contains some invalid ones.');
        } catch (Exception $e) {
            $this->pass('->getLanguages() throws an Exception if the list of given languages contains some invalid ones.');
        }

        // ->getCurrencies()
        $this->diag('->getCurrencies()');
        $currencies_en = $c_en->getCurrencies();
        $currencies_fr = $c_fr->getCurrencies();
        $this->is($currencies_en['EUR'], 'Euro', '->getCurrencies() returns a list of currencies in the language of the localized version');
        $this->is($currencies_fr['EUR'], 'euro', '->getCurrencies() returns a list of currencies in the language of the localized version');
        $this->is($currencies_en, $c_en->Currencies, '->getCurrencies() is equivalent to ->Currencies');

        $currencies = $c_en->getCurrencies(array('USD', 'EUR'));
        $this->is(array_keys($currencies), array('EUR', 'USD'), '->getCurrencies() takes an array of currencies as its first argument');

        try {
            $c_en->getCurrencies(array('USD', 'FRANCS'));
            $this->fail('->getCurrencies() throws an Exception if the list of given currencies contains some invalid ones.');
        } catch (Exception $e) {
            $this->pass('->getCurrencies() throws an Exception if the list of given currencies contains some invalid ones.');
        }

        // ->getCountries()
        $this->diag('->getCountries()');
        $countries_en = $c_en->getCountries();
        $countries_fr = $c_fr->getCountries();
        $this->is($countries_en['ES'], 'Spain', '->getCountries() returns a list of countries in the language of the localized version');
        $this->is($countries_fr['ES'], 'Espagne', '->getCountries() returns a list of countries in the language of the localized version');
        $this->is($countries_en, $c_en->Countries, '->getCountries() is equivalent to ->Countries');

        $countries = $c_en->getCountries(array('FR', 'ES'));
        $this->is(array_keys($countries), array('FR', 'ES'), '->getCountries() takes an array of countries as its first argument');

        try {
            $c_en->getCountries(array('FR', 'EN'));
            $this->fail('->getCountries() throws an Exception if the list of given countries contains some invalid ones.');
        } catch (Exception $e) {
            $this->pass('->getCountries() throws an Exception if the list of given countries contains some invalid ones.');
        }

        // ->getScripts()
        $this->diag('->getScripts()');
        $scripts_en = $c_en->getScripts();
        $scripts_fr = $c_fr->getScripts();
        $this->is($scripts_en['Arab'], 'Arabic', '->getScripts() returns a list of scripts in the language of the localized version');
        $this->is($scripts_fr['Arab'], 'arabe', '->getScripts() returns a list of scripts in the language of the localized version');
        $this->is($scripts_en, $c_en->Scripts, '->getScripts() is equivalent to ->Scripts');

        // ->getTimeZones()
        $this->diag('->getTimeZones()');
        $time_zones_en = $c_en->getTimeZones();
        $time_zones_fr = $c_fr->getTimeZones();

        $this->is($time_zones_en['America/Juneau']['ld'], 'Alaska Daylight Time', '->getTimeZones() returns a list of time zones in the language of the localized version');
        $this->is($time_zones_fr['America/Juneau']['ld'], 'heure avancée de l’Alaska', '->getTimeZones() returns a list of time zones in the language of the localized version');
        $this->is($time_zones_en, $c_en->TimeZones, '->getTimeZones() is equivalent to ->TimeZones');

        // ->validCulture()
        $this->diag('->validCulture()');
        $this->is($c->validCulture('fr'), true, '->validCulture() returns true if the culture is valid');
        $this->is($c->validCulture('fr_FR'), true, '->validCulture() returns true if the culture is valid');
        foreach (array('xxx', 'pp', 'frFR') as $culture) {
            $this->is($c->validCulture($culture), false, '->validCulture() returns false if the culture does not exist');
        }

        // ::getCultures()
        $this->diag('::getCultures()');
        $cultures = sfCultureInfo::getCultures();
        $this->is(in_array('fr', $cultures), true, '::getCultures() returns an array of all available cultures');
        $this->is(in_array('fr_FR', $cultures), true, '::getCultures() returns an array of all available cultures');

        $cultures = sfCultureInfo::getCultures(sfCultureInfo::NEUTRAL);
        $this->is(in_array('fr', $cultures), true, '::getCultures() returns an array of all available cultures');
        $this->is(in_array('fr_FR', $cultures), false, '::getCultures() returns an array of all available cultures');

        $cultures = sfCultureInfo::getCultures(sfCultureInfo::SPECIFIC);
        $this->is(in_array('fr', $cultures), false, '::getCultures() returns an array of all available cultures');
        $this->is(in_array('fr_FR', $cultures), true, '::getCultures() returns an array of all available cultures');

        // ->getParent()
        $this->diag('->getParent()');
        $c = sfCultureInfo::getInstance('fr_FR');
        $this->isa_ok($c->getParent(), 'sfCultureInfo', '->getParent() returns a sfCultureInfo instance');
        $this->is($c->getParent()->getName(), 'fr', '->getParent() returns the parent culture');
        $c = sfCultureInfo::getInstance('fr');
        $this->is($c->getParent()->getName(), 'en', '->getParent() returns the invariant culture if the culture is neutral');

        // ->getIsNeutralCulture()
        $this->diag('->getIsNeutralCulture()');
        $c = sfCultureInfo::getInstance('fr_FR');
        $this->is($c->getIsNeutralCulture(), false, '->getIsNeutralCulture() returns false if the culture is specific');
        $c = sfCultureInfo::getInstance('fr');
        $this->is($c->getIsNeutralCulture(), true, '->getIsNeutralCulture() returns true if the culture is neutral');

        // ->getEnglishName()
        $this->diag('->getEnglishName()');
        $c = sfCultureInfo::getInstance('fr_FR');
        $this->is($c->getEnglishName(), 'French (France)', '->getEnglishName() returns the english name of the current culture');
        $c = sfCultureInfo::getInstance('fr');
        $this->is($c->getEnglishName(), 'French', '->getEnglishName() returns the english name of the current culture');
        $this->is($c->getEnglishName(), $c->EnglishName, '->getEnglishName() is equivalent to ->EnglishName');

        // ->getNativeName()
        $this->diag('->getNativeName()');
        $c = sfCultureInfo::getInstance('fr_FR');
        $this->is($c->getNativeName(), 'français (France)', '->getNativeName() returns the native name of the current culture');
        $c = sfCultureInfo::getInstance('fr');
        $this->is($c->getNativeName(), 'français', '->getNativeName() returns the native name of the current culture');
        $this->is($c->getNativeName(), $c->NativeName, '->getNativeName() is equivalent to ->NativeName');

        // ->getCalendar()
        $this->diag('->getCalendar()');
        $c = sfCultureInfo::getInstance('fr');
        $this->is($c->getCalendar(), 'gregorian', '->getCalendar() returns the default calendar');
        $this->is($c->getCalendar(), $c->Calendar, '->getCalendar() is equivalent to ->Calendar');

        // __get()
        $this->diag('__get()');
        try {
            $c->NonExistant;
            $this->fail('__get() throws an exception if the property does not exist');
        } catch (sfException $e) {
            $this->pass('__get() throws an exception if the property does not exist');
        }

        // __set()
        $this->diag('__set()');
        try {
            $c->NonExistant = 12;
            $this->fail('__set() throws an exception if the property does not exist');
        } catch (sfException $e) {
            $this->pass('__set() throws an exception if the property does not exist');
        }

        // ->getDateTimeFormat()
        $this->diag('->getDateTimeFormat()');
        $c = sfCultureInfo::getInstance();
        $this->isa_ok($c->getDateTimeFormat(), 'sfDateTimeFormatInfo', '->getDateTimeFormat() returns a sfDateTimeFormatInfo instance');

        // ->setDateTimeFormat()
        $this->diag('->setDateTimeFormat()');
        $d = $c->getDateTimeFormat();
        $c->setDateTimeFormat('yyyy');
        $this->is($c->getDateTimeFormat(), 'yyyy', '->setDateTimeFormat() sets the sfDateTimeFormatInfo instance');
        $c->DateTimeFormat = 'mm';
        $this->is($c->getDateTimeFormat(), 'mm', '->setDateTimeFormat() is equivalent to ->DateTimeFormat = ');

        // ->getNumberFormat()
        $this->diag('->getNumberFormat()');
        $c = sfCultureInfo::getInstance();
        $this->isa_ok($c->getNumberFormat(), 'sfNumberFormatInfo', '->getNumberFormat() returns a sfNumberFormatInfo instance');

        // ->setNumberFormat()
        $this->diag('->setNumberFormat()');
        $d = $c->getNumberFormat();
        $c->setNumberFormat('.');
        $this->is($c->getNumberFormat(), '.', '->setNumberFormat() sets the sfNumberFormatInfo instance');
        $c->NumberFormat = '#';
        $this->is($c->getNumberFormat(), '#', '->setNumberFormat() is equivalent to ->NumberFormat = ');
        $c->setNumberFormat(null);
    }
}
