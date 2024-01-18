<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../PhpUnitSfTestHelperTrait.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetFormI18nChoiceCountryTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->configure()
        $this->diag('->configure()');
        try {
            new sfWidgetFormI18nChoiceCountry(array('culture' => 'en', 'countries' => array('EN')));
            $this->fail('->configure() throws an InvalidArgumentException if a country does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->configure() throws an InvalidArgumentException if a country does not exist');
        }

        $v = new sfWidgetFormI18nChoiceCountry(array('culture' => 'en', 'countries' => array('FR', 'GB')));
        $this->is(array_keys($v->getOption('choices')), array('FR', 'GB'), '->configure() can restrict the number of countries with the countries option');

        // ->render()
        $this->diag('->render()');
        $w = new sfWidgetFormI18nChoiceCountry(array('culture' => 'fr'));
        $dom->loadHTML($w->render('country', 'FR'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#country option[value="FR"]')->getValue(), 'France', '->render() renders all countries as option tags');
        $this->is(count($css->matchAll('#country option[value="FR"][selected="selected"]')->getNodes()), 1, '->render() renders all countries as option tags');

        // Test for ICU Upgrade and Ticket #7988
        // should be 0. Tests will break after ICU Update, which is fine. change count to 0
        $this->is(count($css->matchAll('#country option[value="ZZ"]')), 1, '->render() does not contain dummy data');
        $this->is(count($css->matchAll('#country option[value="419"]')), 0, '->render() does not contain region data');

        // add_empty
        $this->diag('add_empty');
        $w = new sfWidgetFormI18nChoiceCountry(array('culture' => 'fr', 'add_empty' => true));
        $dom->loadHTML($w->render('country', 'FR'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#country option[value=""]')->getValue(), '', '->render() renders an empty option if add_empty is true');

        $w = new sfWidgetFormI18nChoiceCountry(array('culture' => 'fr', 'add_empty' => 'foo'));
        $dom->loadHTML($w->render('country', 'FR'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#country option[value=""]')->getValue(), 'foo', '->render() renders an empty option if add_empty is true');
    }
}
