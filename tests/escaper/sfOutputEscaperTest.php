<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../lib/helper/EscapingHelper.php';
require_once __DIR__.'/../fixtures/OutputEscaperTestClassChild.php';
require_once __DIR__.'/../fixtures/OutputEscaperTestClass.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfOutputEscaperTest extends TestCase
{
    protected function setUp(): void
    {
        sfConfig::set('sf_charset', 'UTF-8');
    }

    public function testNotEscapeSpecialValues()
    {
        $this->assertSame(true, null === sfOutputEscaper::escape('esc_entities', null), '::escape() returns null if the value to escape is null');
        $this->assertSame(true, false === sfOutputEscaper::escape('esc_entities', false), '::escape() returns false if the value to escape is false');
        $this->assertSame(true, true === sfOutputEscaper::escape('esc_entities', true), '::escape() returns true if the value to escape is true');
    }

    public function testEscapeRaws()
    {
        $this->assertSame('<strong>escaped!</strong>', sfOutputEscaper::escape('esc_raw', '<strong>escaped!</strong>'), '::escape() takes an escaping strategy function name as its first argument');
    }

    public function testEscapeStrings()
    {
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', sfOutputEscaper::escape('esc_entities', '<strong>escaped!</strong>'), '::escape() returns an escaped string if the value to escape is a string');
        $this->assertSame('&lt;strong&gt;&eacute;chapp&eacute;&lt;/strong&gt;', sfOutputEscaper::escape('esc_entities', '<strong>échappé</strong>'), '::escape() returns an escaped string if the value to escape is a string');
    }

    public function testArray()
    {
        $input = array(
            'foo' => '<strong>escaped!</strong>',
            'bar' => array('foo' => '<strong>escaped!</strong>'),
        );
        $output = sfOutputEscaper::escape('esc_entities', $input);
        $this->assertInstanceOf(sfOutputEscaperArrayDecorator::class, $output, '::escape() returns a sfOutputEscaperArrayDecorator object if the value to escape is an array');
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $output['foo'], '::escape() escapes all elements of the original array');
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $output['bar']['foo'], '::escape() is recursive');
        $this->assertSame($input, $output->getRawValue(), '->getRawValue() returns the unescaped value');
    }

    public function testObjects()
    {
        $input = new OutputEscaperTestClass();
        $output = sfOutputEscaper::escape('esc_entities', $input);
        $this->assertInstanceOf(sfOutputEscaperObjectDecorator::class, $output, '::escape() returns a sfOutputEscaperObjectDecorator object if the value to escape is an object');
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $output->getTitle(), '::escape() escapes all methods of the original object');
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $output->title, '::escape() escapes all properties of the original object');
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $output->getTitleTitle(), '::escape() is recursive');
        $this->assertSame($input, $output->getRawValue(), '->getRawValue() returns the unescaped value');

        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', sfOutputEscaper::escape('esc_entities', $output)->getTitle(), '::escape() does not double escape an object');
        $this->assertInstanceOf(sfOutputEscaperIteratorDecorator::class, sfOutputEscaper::escape('esc_entities', new DirectoryIterator('.')), '::escape() returns a sfOutputEscaperIteratorDecorator object if the value to escape is an object that implements the ArrayAccess interface');
    }

    public function testNotEscapeSafe()
    {
        $this->assertInstanceOf(OutputEscaperTestClass::class, sfOutputEscaper::escape('esc_entities', new sfOutputEscaperSafe(new OutputEscaperTestClass())), '::escape() returns the original value if it is marked as being safe');

        sfOutputEscaper::markClassAsSafe(OutputEscaperTestClass::class);
        $this->assertInstanceOf(OutputEscaperTestClass::class, sfOutputEscaper::escape('esc_entities', new OutputEscaperTestClass()), '::escape() returns the original value if the object class is marked as being safe');
        $this->assertInstanceOf(OutputEscaperTestClassChild::class, sfOutputEscaper::escape('esc_entities', new OutputEscaperTestClassChild()), '::escape() returns the original value if one of the object parent class is marked as being safe');
    }

    public function testEscapeResource()
    {
        $this->expectException(InvalidArgumentException::class);

        $fh = fopen(__FILE__, 'r');
        sfOutputEscaper::escape('esc_entities', $fh);
    }

    public function testUnescapeNotEscapeSpecials()
    {
        $this->assertSame(true, null === sfOutputEscaper::unescape(null), '::unescape() returns null if the value to unescape is null');
        $this->assertSame(true, false === sfOutputEscaper::unescape(false), '::unescape() returns false if the value to unescape is false');
        $this->assertSame(true, true === sfOutputEscaper::unescape(true), '::unescape() returns true if the value to unescape is true');
    }

    public function testUnescapeStrings()
    {
        $this->assertSame('<strong>escaped!</strong>', sfOutputEscaper::unescape('&lt;strong&gt;escaped!&lt;/strong&gt;'), '::unescape() returns an unescaped string if the value to unescape is a string');
        $this->assertSame('<strong>échappé</strong>', sfOutputEscaper::unescape('&lt;strong&gt;&eacute;chapp&eacute;&lt;/strong&gt;'), '::unescape() returns an unescaped string if the value to unescape is a string');
    }

    public function testUnescapeArrays()
    {
        $input = sfOutputEscaper::escape('esc_entities', array(
            'foo' => '<strong>escaped!</strong>',
            'bar' => array('foo' => '<strong>escaped!</strong>'),
        ));

        $output = sfOutputEscaper::unescape($input);
        $this->assertSame(true, is_array($output), '::unescape() returns an array if the input is a sfOutputEscaperArrayDecorator object');
        $this->assertSame('<strong>escaped!</strong>', $output['foo'], '::unescape() unescapes all elements of the original array');
        $this->assertSame('<strong>escaped!</strong>', $output['bar']['foo'], '::unescape() is recursive');
    }

    public function testUnescapeObjects()
    {
        $object = new OutputEscaperTestClass();
        $input = sfOutputEscaper::escape('esc_entities', $object);
        $output = sfOutputEscaper::unescape($input);
        $this->assertInstanceOf(OutputEscaperTestClass::class, $output, '::unescape() returns the original object when a sfOutputEscaperObjectDecorator object is passed');
        $this->assertSame($output->getTitle(), '<strong>escaped!</strong>', '::unescape() unescapes all methods of the original object');
        $this->assertSame($output->title, '<strong>escaped!</strong>', '::unescape() unescapes all properties of the original object');
        $this->assertSame($output->getTitleTitle(), '<strong>escaped!</strong>', '::unescape() is recursive');

        $this->assertInstanceOf(DirectoryIterator::class, sfOutputEscaperIteratorDecorator::unescape(sfOutputEscaper::escape('esc_entities', new DirectoryIterator('.'))), '::unescape() unescapes sfOutputEscaperIteratorDecorator objects');
    }

    public function testUnescapeNotEscapeSafe()
    {
        $this->assertInstanceOf(OutputEscaperTestClass::class, sfOutputEscaper::unescape(sfOutputEscaper::escape('esc_entities', new sfOutputEscaperSafe(new OutputEscaperTestClass()))), '::unescape() returns the original value if it is marked as being safe');

        sfOutputEscaper::markClassAsSafe('OutputEscaperTestClass');
        $this->assertInstanceOf(OutputEscaperTestClass::class, sfOutputEscaper::unescape(sfOutputEscaper::escape('esc_entities', new OutputEscaperTestClass())), '::unescape() returns the original value if the object class is marked as being safe');
        $this->assertInstanceOf(OutputEscaperTestClassChild::class, sfOutputEscaper::unescape(sfOutputEscaper::escape('esc_entities', new OutputEscaperTestClassChild())), '::unescape() returns the original value if one of the object parent class is marked as being safe');
    }

    public function testUnescapeNotEsacpeResource()
    {
        $fh = fopen(__FILE__, 'r');
        $this->assertSame($fh, sfOutputEscaper::unescape($fh), '::unescape() do nothing to resources');
    }

    public function testUnescapeMixedArray()
    {
        $object = new OutputEscaperTestClass();
        $input = array(
            'foo' => 'bar',
            'bar' => sfOutputEscaper::escape('esc_entities', '<strong>bar</strong>'),
            'foobar' => sfOutputEscaper::escape('esc_entities', $object),
        );
        $output = array(
            'foo' => 'bar',
            'bar' => '<strong>bar</strong>',
            'foobar' => $object,
        );
        $this->assertSame($output, sfOutputEscaper::unescape($input), '::unescape() unescapes values with some escaped and unescaped values');
    }
}
