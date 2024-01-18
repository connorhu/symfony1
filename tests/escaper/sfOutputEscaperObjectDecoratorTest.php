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
require_once __DIR__.'/../fixtures/Foo.php';
require_once __DIR__.'/../fixtures/FooCountable.php';
require_once __DIR__.'/../fixtures/OutputEscaperTest.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfOutputEscaperObjectDecoratorTest extends TestCase
{
    private $escaped;

    protected function setUp(): void
    {
        sfConfig::set('sf_charset', 'UTF-8');

        $object = new OutputEscaperTest();
        $this->escaped = sfOutputEscaper::escape('esc_entities', $object);
    }

    public function testSameAsOriginal()
    {
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $this->escaped->getTitle(), 'The escaped object behaves like the real object');

        $array = $this->escaped->getTitles();
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $array[2], 'The escaped object behaves like the real object');
    }

    public function testToString()
    {
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $this->escaped->__toString(), 'The escaped object behaves like the real object');
    }

    public function testSimpleXMLElement()
    {
        $element = new \SimpleXMLElement('<foo>bar</foo>');
        $escaped = sfOutputEscaper::escape('esc_entities', $element);
        $this->assertSame((string) $element, (string) $escaped, '->__toString() is compatible with SimpleXMLElement');
    }

    public function testCountable()
    {
        $foo = sfOutputEscaper::escape('esc_entities', new Foo());
        $fooc = sfOutputEscaper::escape('esc_entities', new FooCountable());
        $this->assertSame(1, count($foo), '->count() returns 1 if the embedded object does not implement the Countable interface');
        $this->assertSame(2, count($fooc), '->count() returns the count() for the embedded object');
    }

    public function testIsset()
    {
        $raw = new stdClass();
        $raw->foo = 'bar';
        $esc = sfOutputEscaper::escape('esc_entities', $raw);
        $this->assertSame(true, isset($esc->foo), '->__isset() asks the wrapped object whether a property is set');
        unset($raw->foo);
        $this->assertSame(true, !isset($esc->foo), '->__isset() asks the wrapped object whether a property is set');
    }
}
