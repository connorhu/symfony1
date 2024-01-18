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
require_once __DIR__.'/../fixtures/TestClass1.php';
require_once __DIR__.'/../fixtures/TestClass2.php';
require_once __DIR__.'/../fixtures/TestClass3.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfOutputEscaperSafeTest extends TestCase
{
    public function testGetValue()
    {
        $safe = new sfOutputEscaperSafe('foo');
        $this->assertSame('foo', $safe->getValue(), '->getValue() returns the embedded value');
    }

    public function testSetGet()
    {
        $safe = new sfOutputEscaperSafe(new TestClass1());

        $this->assertSame('bar', $safe->foo, '->__get() returns the object parameter');
        $safe->foo = 'baz';
        $this->assertSame('baz', $safe->foo, '->__set() sets the object parameter');
    }

    public function testCall()
    {
        $safe = new sfOutputEscaperSafe(new TestClass2());
        $this->assertSame('ok', $safe->doSomething(), '->__call() invokes the embedded method');
    }

    public function testIssetUnset()
    {
        $safe = new sfOutputEscaperSafe(new TestClass3());

        $this->assertSame(true, isset($safe->boolValue), '->__isset() returns true if the property is not null');
        $this->assertSame(false, isset($safe->nullValue), '->__isset() returns false if the property is null');
        $this->assertSame(false, isset($safe->undefinedValue), '->__isset() returns false if the property does not exist');

        unset($safe->boolValue);
        $this->assertSame(false, isset($safe->boolValue), '->__unset() unsets the embedded property');
    }

    public function testIterator()
    {
        $input = array('one' => 1, 'two' => 2, 'three' => 3, 'children' => array(1, 2, 3));
        $output = array();

        $safe = new sfOutputEscaperSafe($input);
        foreach ($safe as $key => $value) {
            $output[$key] = $value;
        }
        $this->assertSame($input, $output, '"Iterator" implementation imitates an array');
    }

    public function testArrayAccess()
    {
        $safe = new sfOutputEscaperSafe(array('foo' => 'bar'));

        $this->assertSame('bar', $safe['foo'], '"ArrayAccess" implementation returns a value from the embedded array');
        $safe['foo'] = 'baz';
        $this->assertSame('baz', $safe['foo'], '"ArrayAccess" implementation sets a value on the embedded array');
        $this->assertSame(true, isset($safe['foo']), '"ArrayAccess" checks if a value is set on the embedded array');
        unset($safe['foo']);
        $this->assertSame(false, isset($safe['foo']), '"ArrayAccess" unsets a value on the embedded array');
    }
}
