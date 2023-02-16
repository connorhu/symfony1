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

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetFormSelectCheckboxTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function choice_callable()
    {
        return array(1, 2, 3);
    }

    public function testTodoMigrate()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->render()
        $this->diag('->render()');
        $w = new sfWidgetFormSelectCheckbox(array('choices' => array('foo' => 'bar', 'foobar' => 'foo'), 'separator' => ''));
        $output = '<ul class="checkbox_list">'.
        '<li><input name="foo[]" type="checkbox" value="foo" id="foo_foo" />&nbsp;<label for="foo_foo">bar</label></li>'.
        '<li><input name="foo[]" type="checkbox" value="foobar" id="foo_foobar" checked="checked" />&nbsp;<label for="foo_foobar">foo</label></li>'.
        '</ul>';
        $this->is($w->render('foo', array('foobar')), $output, '->render() renders a checkbox tag with the value checked');

        // attributes
        $onChange = '<ul class="checkbox_list">'.
        '<li><input name="foo[]" type="checkbox" value="foo" id="foo_foo" onChange="alert(42)" />'.
        '&nbsp;<label for="foo_foo">bar</label></li>'.
        '<li><input name="foo[]" type="checkbox" value="foobar" id="foo_foobar" checked="checked" onChange="alert(42)" />'.
        '&nbsp;<label for="foo_foobar">foo</label></li>'.
        '</ul>';
        $this->is($w->render('foo', array('foobar'), array('onChange' => 'alert(42)')), $onChange, '->render() renders a checkbox tag using extra attributes');

        $w = new sfWidgetFormSelectCheckbox(array('choices' => array('0' => 'bar', '1' => 'foo')));
        $output = <<< 'EOF'
        <ul class="checkbox_list"><li><input name="myname[]" type="checkbox" value="0" id="myname_0" checked="checked" />&nbsp;<label for="myname_0">bar</label></li>
        <li><input name="myname[]" type="checkbox" value="1" id="myname_1" />&nbsp;<label for="myname_1">foo</label></li></ul>
        EOF;
        $this->is($w->render('myname', array(false)), fix_linebreaks($output), '->render() considers false to be an integer 0');

        $w = new sfWidgetFormSelectCheckbox(array('choices' => array('0' => 'bar', '1' => 'foo')));
        $output = <<< 'EOF'
        <ul class="checkbox_list"><li><input name="myname[]" type="checkbox" value="0" id="myname_0" />&nbsp;<label for="myname_0">bar</label></li>
        <li><input name="myname[]" type="checkbox" value="1" id="myname_1" checked="checked" />&nbsp;<label for="myname_1">foo</label></li></ul>
        EOF;
        $this->is($w->render('myname', array(true)), fix_linebreaks($output), '->render() considers true to be an integer 1');

        $w = new sfWidgetFormSelectCheckbox(array('choices' => array()));
        $this->is($w->render('myname', array()), '', '->render() returns an empty HTML string if no choices');

        // group support
        $this->diag('group support');
        $w = new sfWidgetFormSelectCheckbox(array('choices' => array('foo' => array('foo' => 'bar', 'bar' => 'foo'), 'bar' => array('foobar' => 'barfoo'))));
        $output = <<<'EOF'
        foo <ul class="checkbox_list"><li><input name="foo[]" type="checkbox" value="foo" id="foo_foo" checked="checked" />&nbsp;<label for="foo_foo">bar</label></li>
        <li><input name="foo[]" type="checkbox" value="bar" id="foo_bar" />&nbsp;<label for="foo_bar">foo</label></li></ul>
        bar <ul class="checkbox_list"><li><input name="foo[]" type="checkbox" value="foobar" id="foo_foobar" checked="checked" />&nbsp;<label for="foo_foobar">barfoo</label></li></ul>
        EOF;
        $this->is($w->render('foo', array('foo', 'foobar')), fix_linebreaks($output), '->render() has support for groups');

        $w->setOption('choices', array('foo' => array('foo' => 'bar', 'bar' => 'foo')));
        $output = <<<'EOF'
        foo <ul class="checkbox_list"><li><input name="foo[]" type="checkbox" value="foo" id="foo_foo" />&nbsp;<label for="foo_foo">bar</label></li>
        <li><input name="foo[]" type="checkbox" value="bar" id="foo_bar" checked="checked" />&nbsp;<label for="foo_bar">foo</label></li></ul>
        EOF;
        $this->is($w->render('foo', array('bar')), fix_linebreaks($output), '->render() accepts a single group');

        try {
            $w = new sfWidgetFormSelectCheckbox();
            $this->fail('__construct() throws an RuntimeException if you don\'t pass a choices option');
        } catch (RuntimeException $e) {
            $this->pass('__construct() throws an RuntimeException if you don\'t pass a choices option');
        }

        // choices as a callable
        $this->diag('choices as a callable');

        $w = new sfWidgetFormSelectCheckbox(array('choices' => new sfCallable(array($this, 'choice_callable'))));
        $dom->loadHTML($w->render('foo'));
        $css = new sfDomCssSelector($dom);
        $this->is(count($css->matchAll('input[type="checkbox"]')->getNodes()), 3, '->render() accepts a sfCallable as a choices option');

        // choices are translated
        $this->diag('choices are translated');

        $ws = new sfWidgetFormSchema();
        $ws->addFormFormatter('stub', new FormFormatterStub());
        $ws->setFormFormatterName('stub');
        $w = new sfWidgetFormSelectCheckbox(array('choices' => array('foo' => 'bar', 'foobar' => 'foo'), 'separator' => ''));
        $w->setParent($ws);
        $output = '<ul class="checkbox_list">'.
        '<li><input name="foo[]" type="checkbox" value="foo" id="foo_foo" />&nbsp;<label for="foo_foo">translation[bar]</label></li>'.
        '<li><input name="foo[]" type="checkbox" value="foobar" id="foo_foobar" />&nbsp;<label for="foo_foobar">translation[foo]</label></li>'.
        '</ul>';
        $this->is($w->render('foo'), $output, '->render() translates the options');

        // choices are escaped
        $this->diag('choices are escaped');

        $w = new sfWidgetFormSelectCheckbox(array('choices' => array('<b>Hello world</b>')));
        $this->is($w->render('foo'), '<ul class="checkbox_list"><li><input name="foo[]" type="checkbox" value="0" id="foo_0" />&nbsp;<label for="foo_0">&lt;b&gt;Hello world&lt;/b&gt;</label></li></ul>', '->render() escapes the choices');

        // __clone()
        $this->diag('__clone()');
        $w = new sfWidgetFormSelectCheckbox(array('choices' => new sfCallable(array($w, 'foo'))));
        $w1 = clone $w;
        $callable = $w1->getOption('choices')->getCallable();
        $this->is(spl_object_hash($callable[0]), spl_object_hash($w1), '__clone() changes the choices is a callable and the object is an instance of the current object');

        $w = new sfWidgetFormSelectCheckbox(array('choices' => new sfCallable(array($a = new stdClass(), 'foo'))));
        $w1 = clone $w;
        $callable = $w1->getOption('choices')->getCallable();
        $this->is(spl_object_hash($callable[0]), spl_object_hash($a), '__clone() changes nothing if the choices is a callable and the object is not an instance of the current object');
    }
}
