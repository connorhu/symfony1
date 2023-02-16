<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class sfNoCacheTest extends TestCase
{
    public function testGet()
    {
        $cache = new sfNoCache();
        $this->assertSame(null, $cache->get('foo'), '->get() always returns "null"');
    }

    public function testSet()
    {
        $cache = new sfNoCache();
        $this->assertTrue($cache->set('foo', 'bar'), '->set() always returns "true"');
    }

    public function testHas()
    {
        $cache = new sfNoCache();
        $cache->set('foo', 'bar');
        $this->assertSame(false, $cache->has('foo'), '->has() always returns "false"');
    }

    public function testRemove()
    {
        $cache = new sfNoCache();
        $this->assertTrue($cache->remove('foo'), '->remove() always returns "true"');
    }

    public function testRemovePattern()
    {
        $cache = new sfNoCache();
        $this->assertTrue($cache->removePattern('**'), '->removePattern() always returns "true"');
    }

    public function testClean()
    {
        $cache = new sfNoCache();
        $this->assertTrue($cache->clean(), '->clean() always returns "true"');
    }

    public function testGetLastModified()
    {
        $cache = new sfNoCache();
        $this->assertSame(0, $cache->getLastModified('foo'), '->getLastModified() always returns "0"');
    }

    public function testGetTimeout()
    {
        $cache = new sfNoCache();
        $this->assertSame(0, $cache->getTimeout('foo'), '->getTimeout() always returns "0"');
    }
}
