<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

abstract class CacheDriverTestCase extends TestCase
{
    protected sfCache $cache;

    public function testSetGetHas()
    {
        $data = 'some random data to store in the cache system... (\'"!#/é$£)';

        $this->assertTrue($this->cache->set('test', $data, 86400));
        $this->assertSame($data, $this->cache->get('test'), '->get() retrieves data form the cache');
        $this->assertTrue($this->cache->has('test'), '->has() returns true if the cache exists');

        $this->assertTrue($this->cache->set('test', $data, -10), '->set() takes a lifetime as its third argument');
        $this->assertSame('default', $this->cache->get('test', 'default'), '->get() returns the default value if cache has expired');
        $this->assertSame(false, $this->cache->has('test'), '->has() returns true if the cache exists');

        $this->assertSame(null, $this->cache->get('foo'), '->get() returns null if the cache does not exist');
        $this->assertSame('default', $this->cache->get('foo', 'default'), '->get() takes a default value as its second argument');
        $this->assertSame(false, $this->cache->has('foo'), '->has() returns false if the cache does not exist');

        $data = 'another some random data to store in the cache system...';
        $this->assertTrue($this->cache->set('test', $data), '->set() overrides previous data stored in the cache');
        $this->assertSame($data, $this->cache->get('test'), '->get() retrieves the latest data form the cache');

        $data = false;
        $this->assertTrue($this->cache->set('test', $data), '->set() false data are stored in the cache');
        $this->assertTrue($this->cache->has('test'), '->has() returns true if the cache exists with false value');
        $this->assertSame($data, $this->cache->get('test'), '->get() retrieves the latest data form the cache');
        $this->assertSame($data, $this->cache->get('test', 'foo'), '->get() does not return default value if false is stored');

        $this->cache->clean();
        $this->cache->set('foo', 'foo');
        $this->cache->set('foo:bar', 'bar');
        $this->cache->set('foo:bar:foo:bar:foo', 'foobar');
        $this->assertSame('foo', $this->cache->get('foo'), '->set() accepts a "namespaced" cache key');
        $this->assertSame('bar', $this->cache->get('foo:bar'), '->set() accepts a "namespaced" cache key');
        $this->assertSame('foobar', $this->cache->get('foo:bar:foo:bar:foo'), '->set() accepts a "namespaced" cache key');
    }

    public function testClean()
    {
        $data = 'some random data to store in the cache system...';
        $this->cache->set('foo', $data, -10);
        $this->cache->set('bar', $data, 86400);

        $this->cache->clean(sfCache::OLD);
        $this->assertSame(false, $this->cache->has('foo'), '->clean() cleans old cache key if given the sfCache::OLD argument');
        $this->assertTrue($this->cache->has('bar'), '->clean() cleans old cache key if given the sfCache::OLD argument');

        $this->cache->set('foo', $data, -10);
        $this->cache->set('bar', $data, 86400);

        $this->cache->clean(sfCache::ALL);
        $this->assertSame(false, $this->cache->has('foo'), '->clean() cleans all cache key if given the sfCache::ALL argument');
        $this->assertSame(false, $this->cache->has('bar'), '->clean() cleans all cache key if given the sfCache::ALL argument');

        $this->cache->set('foo', $data, -10);
        $this->cache->set('bar', $data, 86400);

        $this->cache->clean();
        $this->assertSame(false, $this->cache->has('foo'), '->clean() cleans all cache key if given no argument');
        $this->assertSame(false, $this->cache->has('bar'), '->clean() cleans all cache key if given no argument');

        $this->cache->clean();
        $this->cache->setOption('automatic_cleaning_factor', 1);
        $this->cache->set('foo', $data);
        $this->cache->set('foo', $data);
        $this->cache->set('foo', $data);
        $this->cache->setOption('automatic_cleaning_factor', 1000);
    }

    public function testRemove()
    {
        $data = 'some random data to store in the cache system...';
        $this->cache->clean();
        $this->cache->set('foo', $data);
        $this->cache->set('bar', $data);

        $this->cache->remove('foo');
        $this->assertSame(false, $this->cache->has('foo'), '->remove() takes a cache key as its first argument');
        $this->assertSame(null, $this->cache->get('foo'), '->remove() takes a cache key as its first argument');
        $this->assertTrue($this->cache->has('bar'), '->remove() takes a cache key as its first argument');
    }

    /**
     * @dataProvider removePatternDataProvider
     */
    public function testRemovePattern(string $pattern, array $results)
    {
        $this->cache->clean();

        $this->cache->set('foo:bar:foo', 'foo');
        $this->cache->set('bar:bar:foo', 'foo');
        $this->cache->set('foo:bar:foo1', 'foo');
        $this->cache->set('foo:bar:foo:bar', 'foo');

        $this->cache->removePattern($pattern);

        $this->assertSame($results[0], $this->cache->has('foo:bar:foo'), '->removePattern() takes a pattern as its first argument');
        $this->assertSame($results[1], $this->cache->has('bar:bar:foo'), '->removePattern() takes a pattern as its first argument');
        $this->assertSame($results[2], $this->cache->has('foo:bar:foo1'), '->removePattern() takes a pattern as its first argument');
        $this->assertSame($results[3], $this->cache->has('foo:bar:foo:bar'), '->removePattern() takes a pattern as its first argument');
    }

    public function removePatternDataProvider(): Generator
    {
        yield array('*:bar:foo',  array(false, false, true, true));
        yield array('foo:bar:*',  array(false, true, false, true));
        yield array('foo:**:foo', array(false, true, true, true));
        yield array('foo:bar:**', array(false, true, false, false));
        yield array('**:bar',     array(true, true, true, false));
        yield array('**',         array(false, false, false, false));
    }

    public function testGetTimeout()
    {
        foreach (array(86400, 10) as $lifetime) {
            $this->cache->set('foo', 'bar', $lifetime);

            $delta = $this->cache->getTimeout('foo') - time();
            $this->assertTrue($delta >= $lifetime - 1 && $delta <= $lifetime, '->getTimeout() returns the timeout time for a given cache key');
        }

        $this->cache->set('bar', 'foo', -10);
        $this->assertSame($this->cache->getTimeout('bar'), 0, '->getTimeout() returns the timeout time for a given cache key');

        foreach (array(86400, 10) as $lifetime) {
            $this->cache->setOption('lifetime', $lifetime);
            $this->cache->set('foo', 'bar');

            $delta = $this->cache->getTimeout('foo') - time();
            $this->assertTrue($delta >= $lifetime - 1 && $delta <= $lifetime, '->getTimeout() returns the timeout time for a given cache key');
        }

        $this->assertSame(0, $this->cache->getTimeout('nonexistantkey'), '->getTimeout() returns 0 if the cache key does not exist');
    }

    public function testGetLastModified()
    {
        foreach (array(86400, 10) as $lifetime) {
            $this->cache->set('bar', 'foo', $lifetime);
            $now = time();
            $lastModified = $this->cache->getLastModified('bar');
            $this->assertTrue($lastModified >= time() - 1 && $lastModified <= time(), '->getLastModified() returns the last modified time for a given cache key');
        }

        $this->cache->set('bar', 'foo', -10);
        $this->assertSame(0, $this->cache->getLastModified('bar'), '->getLastModified() returns the last modified time for a given cache key');

        foreach (array(86400, 10) as $lifetime) {
            $this->cache->setOption('lifetime', $lifetime);
            $this->cache->set('bar', 'foo');

            $now = time();
            $lastModified = $this->cache->getLastModified('bar');
            $this->assertSame($lastModified >= time() - 1 && $lastModified <= time(), true, '->getLastModified() returns the last modified time for a given cache key');
        }

        $this->assertSame(0, $this->cache->getLastModified('nonexistantkey'), '->getLastModified() returns 0 if the cache key does not exist');
    }

    public function testGetMany()
    {
        $this->cache->clean();

        $this->cache->set('bar', 'foo');
        $this->cache->set('foo', 'bar');

        $result = $this->cache->getMany(array('foo', 'bar'));
        asort($result);
        $this->assertSame(array('foo', 'bar'), array_keys($result), '->getMany() gets many keys in one call');
        $this->assertSame('bar', $result['foo'], '->getMany() gets many keys in one call');
        $this->assertSame('foo', $result['bar'], '->getMany() gets many keys in one call');

        $this->cache->clean();
    }
}
