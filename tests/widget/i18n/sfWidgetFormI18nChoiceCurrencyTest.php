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
class sfWidgetFormI18nChoiceCurrencyTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->configure()
        $this->diag('->configure()');
        try {
            new sfWidgetFormI18nChoiceCurrency(array('culture' => 'en', 'currencies' => array('xx')));
            $this->fail('->configure() throws an InvalidArgumentException if a currency does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->configure() throws an InvalidArgumentException if a currency does not exist');
        }

        $v = new sfWidgetFormI18nChoiceCurrency(array('culture' => 'en', 'currencies' => array('EUR', 'USD')));
        $this->is(array_keys($v->getOption('choices')), array('EUR', 'USD'), '->configure() can restrict the number of currencies with the currencies option');

        // ->render()
        $this->diag('->render()');
        $w = new sfWidgetFormI18nChoiceCurrency(array('culture' => 'fr'));
        $dom->loadHTML($w->render('currency', 'EUR'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#currency option[value="EUR"]')->getValue(), 'euro', '->render() renders all currencies as option tags');
        $this->is(count($css->matchAll('#currency option[value="EUR"][selected="selected"]')->getNodes()), 1, '->render() renders all currencies as option tags');

        // Test for ICU Upgrade
        // should be 0. Test will break after ICU Update, which is fine. change count to 0
        $this->is(count($css->matchAll('#currency option[value="XXX"]')), 1, '->render() does not output ICU dummy data');

        // add_empty
        $this->diag('add_empty');
        $w = new sfWidgetFormI18nChoiceCurrency(array('culture' => 'fr', 'add_empty' => true));
        $dom->loadHTML($w->render('currency', 'EUR'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#currency option[value=""]')->getValue(), '', '->render() renders an empty option if add_empty is true');

        $w = new sfWidgetFormI18nChoiceCurrency(array('culture' => 'fr', 'add_empty' => 'foo'));
        $dom->loadHTML($w->render('currency', 'EUR'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#currency option[value=""]')->getValue(), 'foo', '->render() renders an empty option if add_empty is true');
    }
}
