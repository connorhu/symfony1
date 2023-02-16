<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/myException.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfExceptionsTest extends TestCase
{
    public function testInheritance()
    {
        foreach (array(
            'cache', 'configuration', 'controller', 'database',
            'error404', 'factory', 'file', 'filter', 'forward', 'initialization', 'parse', 'render', 'security',
            'stop', 'storage', 'view',
        ) as $class) {
            $class = sprintf('sf%sException', ucfirst($class));
            $e = new $class();
            $this->assertSame(true, $e instanceof sfException, sprintf('"%s" inherits from sfException', $class));
        }
    }

    public function testFormatArgs()
    {
        $this->assertSame("'foo'", myException::formatArgsTest('foo', true), 'formatArgs() can format a single argument');
        $this->assertSame("'foo', 'bar'", myException::formatArgsTest(array('foo', 'bar')), 'formatArgs() can format an array of arguments');
        $this->assertSame("<em>object</em>('stdClass')", myException::formatArgsTest(new stdClass(), true), 'formatArgs() can format an objet instance');
        $this->assertSame('<em>null</em>', myException::formatArgsTest(null, true), 'formatArgs() can format a null');
        $this->assertSame('100', myException::formatArgsTest(100, true), 'formatArgs() can format an integer');
        $this->assertSame("<em>array</em>('foo' => <em>object</em>('stdClass'), 'bar' => 2)", myException::formatArgsTest(array('foo' => new stdClass(), 'bar' => 2), true), 'formatArgs() can format a nested array');

        $this->assertSame("'&amp;'", myException::formatArgsTest('&', true), 'formatArgs() escapes strings');
        $this->assertSame("<em>array</em>('&amp;' => '&amp;')", myException::formatArgsTest(array('&' => '&'), true), 'formatArgs() escapes strings for keys and values in arrays');
    }
}
