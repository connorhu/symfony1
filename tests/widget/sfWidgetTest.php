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
require_once __DIR__.'/../fixtures/MyWidget4.php';
require_once __DIR__.'/../fixtures/MyWidgetWithRequired.php';
require_once __DIR__.'/../fixtures/MyClass.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        $w = new MyWidget4();
        $this->is($w->getAttributes(), array(), '->__construct() can take no argument');
        $w = new MyWidget4(array(), array('class' => 'foo'));
        $this->is($w->getAttributes(), array('class' => 'foo'), '->__construct() can take an array of default HTML attributes');

        try {
            new MyWidget4(array('nonexistant' => false));
            $this->fail('__construct() throws an InvalidArgumentException if you pass some non existant options');
            $this->skip();
        } catch (InvalidArgumentException $e) {
            $this->pass('__construct() throws an InvalidArgumentException if you pass some non existant options');
            $this->like($e->getMessage(), '/ \'nonexistant\'/', 'The exception contains the non existant option names');
        }

        $this->diag('getRequiredOptions');
        $w = new MyWidgetWithRequired(array('foo' => 'bar'));
        $this->is($w->getRequiredOptions(), array('foo'), '->getRequiredOptions() returns an array of required option names');

        try {
            new MyWidgetWithRequired();
            $this->fail('__construct() throws an RuntimeException if you don\'t pass a required option');
        } catch (RuntimeException $e) {
            $this->pass('__construct() throws an RuntimeException if you don\'t pass a required option');
        }

        $w = new MyWidget4();

        // ->getOption() ->setOption() ->setOptions() ->getOptions() ->hasOption()
        $this->diag('->getOption() ->setOption() ->setOptions() ->getOptions() ->hasOption()');
        $w->setOption('foo', 'bar');
        $this->is($w->getOption('foo'), 'bar', '->setOption() sets an option value');
        $this->is($w->getOption('nonexistant'), null, '->getOption() returns null if the option does not exist');
        $this->is($w->getOption('nonexistant', 'default value'), 'default value', '->getOption() returns default value if the option does not exist');
        $this->is($w->hasOption('foo'), true, '->hasOption() returns true if the option exist');
        $this->is($w->hasOption('nonexistant'), false, '->hasOption() returns false if the option does not exist');
        try {
            $w->setOption('foobar', 'foo');
            $this->fail('->setOption() throws an InvalidArgumentException if the option is not registered');
        } catch (InvalidArgumentException $e) {
            $this->pass('->setOption() throws an InvalidArgumentException if the option is not registered');
        }

        // ->addOption()
        $this->diag('->addOption()');
        $w->addOption('foobar');
        $w->setOption('foobar', 'bar');
        $this->is($w->getOption('foobar'), 'bar', '->addOption() adds a new option');

        $w = new MyWidget4();
        $w->setOptions(array('foo' => 'bar'));
        $this->is($w->getOptions(), array('foo' => 'bar'), '->getOptions() returns an array of all options');

        $w = new MyWidget4();

        // ->setAttribute() ->getAttribute()
        $this->diag('->setAttribute() ->getAttribute()');
        $w->setAttribute('foo', 'bar');
        $this->is($w->getAttribute('foo'), 'bar', '->setAttribute() sets a new default attribute for the widget');

        // ->getAttributes()
        $this->diag('->getAttributes()');
        $this->is($w->getAttributes(), array('foo' => 'bar'), '->getAttributes() returns an array of attributes');

        // ->setAttributes()
        $this->diag('->setAttributes()');
        $w->setAttributes(array('foo' => 'bar'));
        $this->is($w->getAttributes(), array('foo' => 'bar'), '->setAttributes() sets attributes');

        // ->attributesToHtml()
        $this->diag('->attributesToHtml()');
        $w = new MyWidget4(array(), array('foo' => 'bar', 'foobar' => '<strong>été</strong>'));
        $this->is($w->render('foo', 'bar'), ' foo="bar" foobar="&lt;strong&gt;été&lt;/strong&gt;"', '->attributesToHtml() converts an attribute array to an HTML attribute string');

        // ->renderTag()
        $this->diag('->renderTag()');
        $w = new MyWidget4(array(), array('foo' => 'bar'));
        $this->is($w->renderTag('input', array('bar' => 'foo')), '<input foo="bar" bar="foo" />', '->renderTag() renders a HTML tag with attributes');
        $this->is($w->renderTag(''), '', '->renderTag() renders an empty string if the tag name is empty');

        // ->renderContentTag()
        $this->diag('->renderContentTag()');
        $w = new MyWidget4(array(), array('foo' => 'bar'));
        $this->is($w->renderContentTag('textarea', 'content', array('bar' => 'foo')), '<textarea foo="bar" bar="foo">content</textarea>', '->renderContentTag() renders a HTML tag with content and attributes');
        $this->is($w->renderContentTag(''), '', '->renderContentTag() renders an empty string if the tag name is empty');

        // ::escapeOnce()
        $this->diag('::escapeOnce()');
        $this->is(sfWidget::escapeOnce('This a > text to "escape"'), 'This a &gt; text to &quot;escape&quot;', '::escapeOnce() escapes an HTML strings');
        $this->is(sfWidget::escapeOnce(sfWidget::escapeOnce('This a > text to "escape"')), 'This a &gt; text to &quot;escape&quot;', '::escapeOnce() does not escape an already escaped string');
        $this->is(sfWidget::escapeOnce('This a &gt; text to "escape"'), 'This a &gt; text to &quot;escape&quot;', '::escapeOnce() does not escape an already escaped string');

        $this->is(sfWidget::escapeOnce(new MyClass()), 'mycontent', '::escapeOnce() converts objects to string');

        // ::fixDoubleEscape()
        $this->diag('::fixDoubleEscape()');
        $this->is(sfWidget::fixDoubleEscape(htmlspecialchars(htmlspecialchars('This a > text to "escape"'), ENT_QUOTES, sfWidget::getCharset()), ENT_QUOTES, sfWidget::getCharset()), 'This a &gt; text to &quot;escape&quot;', '::fixDoubleEscape() fixes double escaped strings');

        // ::getCharset() ::setCharset()
        $this->diag('::getCharset() ::setCharset()');
        sfWidget::setCharset('UTF-8');
        $this->is(sfWidget::getCharset(), 'UTF-8', '::getCharset() returns the charset to use for widgets');
        sfWidget::setCharset('ISO-8859-1');
        $this->is(sfWidget::getCharset(), 'ISO-8859-1', '::setCharset() changes the charset to use for widgets');

        // ::setXhtml() ::isXhtml()
        $this->diag('::setXhtml() ::isXhtml()');
        $w = new MyWidget4();
        $this->is(sfWidget::isXhtml(), true, '::isXhtml() return true if the widget must returns XHTML tags');
        sfWidget::setXhtml(false);
        $this->is($w->renderTag('input', array('value' => 'Test')), '<input value="Test">', '::setXhtml() changes the value of the XHTML tag');

        // ->getJavaScripts() ->getStylesheets()
        $this->diag('->getJavaScripts() ->getStylesheets()');
        $w = new MyWidget4();
        $this->is($w->getJavaScripts(), array(), '->getJavaScripts() returns an array of stylesheets');
        $this->is($w->getStylesheets(), array(), '->getStylesheets() returns an array of JavaScripts');
    }
}
