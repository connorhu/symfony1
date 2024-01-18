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
require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../../lib/helper/TagHelper.php';

/**
 * @internal
 *
 * @coversNothing
 */
class TagHelperTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $context = sfContext::getInstance();

        // tag()
        $this->diag('tag()');
        $this->is(tag(''), '', 'tag() returns an empty string with empty input');
        $this->is(tag('br'), '<br />', 'tag() takes a tag as its first parameter');
        $this->is(tag('p', null, true), '<p>', 'tag() takes a boolean parameter as its third parameter');
        $this->is(tag('br', array('class' => 'foo'), false), '<br class="foo" />', 'tag() takes an array of options as its second parameters');
        $this->is(tag('br', 'class=foo', false), '<br class="foo" />', 'tag() takes a string of options as its second parameters');
        $this->is(tag('p', array('class' => 'foo', 'id' => 'bar'), true), '<p class="foo" id="bar">', 'tag() takes a boolean parameter as its third parameter');
        // $this->is(tag('br', array('class' => '"foo"')), '<br class="&quot;foo&quot;" />');

        // content_tag()
        $this->diag('content_tag()');
        $this->is(content_tag(''), '', 'content_tag() returns an empty string with empty input');
        $this->is(content_tag('', ''), '', 'content_tag() returns an empty string with empty input');
        $this->is(content_tag('p', 'Toto'), '<p>Toto</p>', 'content_tag() takes a content as its second parameter');
        $this->is(content_tag('p', ''), '<p></p>', 'content_tag() takes a tag as its first parameter');

        // cdata_section()
        $this->diag('cdata_section()');
        $this->is(cdata_section(''), '<![CDATA[]]>', 'cdata_section() returns a string wrapped into a CDATA section');
        $this->is(cdata_section('foobar'), '<![CDATA[foobar]]>', 'cdata_section() returns a string wrapped into a CDATA section');

        // escape_javascript()
        $this->diag('escape_javascript()');
        $this->is(escape_javascript("alert('foo');\nalert(\"bar\");"), 'alert(\\\'foo\\\');\\nalert(\\"bar\\");', 'escape_javascript() escapes JavaScript scripts');

        // _get_option()
        $this->diag('_get_option()');
        $options = array(
            'foo' => 'bar',
            'bar' => 'foo',
        );
        $this->is(_get_option($options, 'foo'), 'bar', '_get_option() returns the value for the given key');
        $this->ok(!isset($options['foo']), '_get_option() removes the key from the original array');
        $this->is(_get_option($options, 'nofoo', 'nobar'), 'nobar', '_get_option() returns the default value if the key does not exist');

        // escape_once()
        $this->diag('escape_once()');
        $this->is(escape_once('This a > text to "escape"'), 'This a &gt; text to &quot;escape&quot;', 'escape_once() escapes an HTML strings');
        $this->is(escape_once(escape_once('This a > text to "escape"')), 'This a &gt; text to &quot;escape&quot;', 'escape_once() does not escape an already escaped string');
        $this->is(escape_once('This a &gt; text to "escape"'), 'This a &gt; text to &quot;escape&quot;', 'escape_once() does not escape an already escaped string');
        $this->is(escape_once("This a &gt; \"text\" to 'escape'"), "This a &gt; &quot;text&quot; to 'escape'", 'escape_once() does not escape simple quotes but escape double quotes');

        // fix_double_escape()
        $this->diag('fix_double_escape()');
        $this->is(fix_double_escape(htmlspecialchars(htmlspecialchars('This a > text to "escape"'), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'), 'This a &gt; text to &quot;escape&quot;', 'fix_double_escape() fixes double escaped strings');
    }
}
