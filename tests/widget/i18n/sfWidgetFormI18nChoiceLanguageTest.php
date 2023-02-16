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
class sfWidgetFormI18nChoiceLanguageTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->configure()
        $this->diag('->configure()');
        try {
            new sfWidgetFormI18nChoiceLanguage(array('culture' => 'en', 'languages' => array('xx')));
            $this->fail('->configure() throws an InvalidArgumentException if a language does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->configure() throws an InvalidArgumentException if a language does not exist');
        }

        $v = new sfWidgetFormI18nChoiceLanguage(array('culture' => 'en', 'languages' => array('fr', 'en')));
        $this->is(array_keys($v->getOption('choices')), array('en', 'fr'), '->configure() can restrict the number of languages with the languages option');

        // ->render()
        $this->diag('->render()');
        $w = new sfWidgetFormI18nChoiceLanguage(array('culture' => 'fr'));
        $dom->loadHTML($w->render('language', 'en'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#language option[value="en"]')->getValue(), 'anglais', '->render() renders all languages as option tags');
        $this->is(count($css->matchAll('#language option[value="en"][selected="selected"]')->getNodes()), 1, '->render() renders all languages as option tags');

        // add_empty
        $this->diag('add_empty');
        $w = new sfWidgetFormI18nChoiceLanguage(array('culture' => 'fr', 'add_empty' => true));
        $dom->loadHTML($w->render('language', 'FR'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#language option[value=""]')->getValue(), '', '->render() renders an empty option if add_empty is true');

        $w = new sfWidgetFormI18nChoiceLanguage(array('culture' => 'fr', 'add_empty' => 'foo'));
        $dom->loadHTML($w->render('language', 'FR'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#language option[value=""]')->getValue(), 'foo', '->render() renders an empty option if add_empty is true');
    }
}
