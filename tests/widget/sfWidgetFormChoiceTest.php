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
require_once __DIR__.'/../fixtures/FormFormatterStub.php';
require_once __DIR__.'/../fixtures/MyWidget3.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetFormChoiceTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->getRenderer()
        $this->diag('->getRenderer()');
        $w = new sfWidgetFormChoice(array('choices' => array()));
        $this->is(get_class($w->getRenderer()), 'sfWidgetFormSelect', '->getRenderer() guesses the renderer class to use');
        $w->setOption('multiple', true);
        $this->is(get_class($w->getRenderer()), 'sfWidgetFormSelect', '->getRenderer() guesses the renderer class to use');
        $w->setOption('expanded', true);
        $this->is(get_class($w->getRenderer()), 'sfWidgetFormSelectCheckbox', '->getRenderer() guesses the renderer class to use');
        $w->setOption('multiple', false);
        $this->is(get_class($w->getRenderer()), 'sfWidgetFormSelectRadio', '->getRenderer() guesses the renderer class to use');

        $w->setOption('renderer_class', 'MyWidget3');
        $this->is(get_class($w->getRenderer()), 'MyWidget3', '->getRenderer() uses the renderer_class as the widget class if provided');

        $w->setOption('renderer_class', null);
        $w->setOption('renderer', new MyWidget3(array('choices' => array())));
        $this->is(get_class($w->getRenderer()), 'MyWidget3', '->getRenderer() uses the renderer as the widget if provided');

        // ->render()
        $this->diag('->render()');
        $w = new sfWidgetFormChoice(array('choices' => array('foo' => 'bar')));
        $this->like($w->render('foo'), '/<select name="foo" id="foo">/', '->render() renders a select tag by default');
        $w->setIdFormat('barID_%s');
        $this->like($w->render('foo'), '/<select name="foo" id="barID_foo">/', '->render() uses the id format specified');
        $w->setIdFormat('%s');
        $w->setOption('multiple', true);
        $this->like($w->render('foo'), '/<select name="foo\[\]" multiple="multiple" id="foo">/', '->render() adds a multiple attribute for multiple selects');
        $w->setOption('expanded', true);
        $this->like($w->render('foo'), '/<ul class="checkbox_list">/', '->render() uses a checkbox list when expanded and multiple are true');
        $w->setOption('multiple', false);
        $this->like($w->render('foo'), '/<ul class="radio_list">/', '->render() uses a checkbox list when expanded is true and multiple is false');

        // choices are translated
        $this->diag('choices are translated');

        $ws = new sfWidgetFormSchema();
        $ws->addFormFormatter('stub', new FormFormatterStub());
        $ws->setFormFormatterName('stub');
        $w = new sfWidgetFormChoice(array('choices' => array('foo' => 'bar', 'foobar' => 'foo')));
        $w->setParent($ws);
        $dom->loadHTML($w->render('foo'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo option[value="foo"]')->getValue(), 'translation[bar]', '->render() translates the options');
        $this->is($css->matchSingle('#foo option[value="foobar"]')->getValue(), 'translation[foo]', '->render() translates the options');

        // choices are not translated if "translate_choices" is set to false
        $this->diag('choices are not translated if "translate_choices" is set to false');

        $ws = new sfWidgetFormSchema();
        $ws->addFormFormatter('stub', new FormFormatterStub());
        $ws->setFormFormatterName('stub');
        $w = new sfWidgetFormChoice(array('choices' => array('foo' => 'bar', 'foobar' => 'foo'), 'translate_choices' => false));
        $w->setParent($ws);
        $dom->loadHTML($w->render('foo'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo option[value="foo"]')->getValue(), 'bar', '->render() translates the options');
        $this->is($css->matchSingle('#foo option[value="foobar"]')->getValue(), 'foo', '->render() translates the options');

        // ->getJavaScripts() ->getStylesheets()
        $this->diag('->getJavaScripts() ->getStylesheets()');
        $w = new sfWidgetFormChoice(array('choices' => array()));
        $w->setOption('renderer_class', 'MyWidget3');
        $this->is($w->getJavaScripts(), array('/path/to/a/file.js'), '->getJavaScripts() returns the stylesheets of the renderer widget');
        $this->is($w->getStylesheets(), array('/path/to/a/file.css' => 'all'), '->getStylesheets() returns the JavaScripts of the renderer widget');

        // __clone()
        $this->diag('__clone()');
        $w = new sfWidgetFormChoice(array('choices' => new sfCallable(array($w, 'foo'))));
        $w1 = clone $w;
        $callable = $w1->getOption('choices')->getCallable();
        $this->is(spl_object_hash($callable[0]), spl_object_hash($w1), '__clone() changes the choices is a callable and the object is an instance of the current object');

        $w = new sfWidgetFormChoice(array('choices' => new sfCallable(array($a = new stdClass(), 'foo'))));
        $w1 = clone $w;
        $callable = $w1->getOption('choices')->getCallable();
        $this->is(spl_object_hash($callable[0]), spl_object_hash($a), '__clone() changes nothing if the choices is a callable and the object is not an instance of the current object');
    }
}
