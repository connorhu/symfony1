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
class sfWidgetFormI18nChoiceTimezoneTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->render()
        $this->diag('->render()');
        $w = new sfWidgetFormI18nChoiceTimezone();
        $dom->loadHTML($w->render('timezone', 'Europe/Paris'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#timezone option[value="Europe/Paris"]')->getValue(), 'Europe/Paris', '->render() renders all timezones as option tags');
        $this->is(count($css->matchAll('#timezone option[value="Europe/Paris"][selected="selected"]')->getNodes()), 1, '->render() renders all timezones as option tags');

        // add_empty
        $this->diag('add_empty');
        $w = new sfWidgetFormI18nChoiceTimezone(array('culture' => 'fr', 'add_empty' => true));
        $dom->loadHTML($w->render('language', 'FR'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#language option[value=""]')->getValue(), '', '->render() renders an empty option if add_empty is true');

        $w = new sfWidgetFormI18nChoiceTimezone(array('culture' => 'fr', 'add_empty' => 'foo'));
        $dom->loadHTML($w->render('language', 'FR'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#language option[value=""]')->getValue(), 'foo', '->render() renders an empty option if add_empty is true');
    }
}
