<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/CacheDriverTestCase.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfMemcacheCacheTest extends CacheDriverTestCase
{
    public function setUp(): void
    {
        $memcachedHost = getenv('MEMCACHED_HOST');
        if (!$memcachedHost) {
            $memcachedHost = null;
        }

        $this->cache = new sfMemcacheCache(array(
            'storeCacheInfo' => true,
            'host' => $memcachedHost,
        ));
    }

    public function testRemove()
    {
        parent::testRemove();

        $backend = $this->cache->getBackend();
        $prefix = $this->cache->getOption('prefix');
        $this->cache->clean();
        $this->cache->set('test_1', 'abc');
        $this->cache->set('test_2', 'abc');
        $this->cache->remove('test_1');

        $cacheInfo = $backend->get($prefix.'_metadata');

        $this->assertTrue(is_array($cacheInfo), 'Cache info is an array');
        $this->assertSame(1, count($cacheInfo), 'Cache info contains 1 element');
        $this->assertTrue(!in_array($prefix.'test_1', $cacheInfo), 'Cache info no longer contains the removed key');
        $this->assertTrue(in_array($prefix.'test_2', $cacheInfo), 'Cache info still contains the key that was not removed');
    }

    // ->removePattern() test for ticket #6220
    public function testRemovePattern6220()
    {
        $backend = $this->cache->getBackend();
        $prefix = $this->cache->getOption('prefix');
        $this->cache->clean();
        $this->cache->set('test_1', 'abc');
        $this->cache->set('test_2', 'abc');
        $this->cache->set('test3', 'abc');
        $this->cache->removePattern('test_*');

        $cacheInfo = $backend->get($prefix.'_metadata');
        $this->assertTrue(is_array($cacheInfo), 'Cache info is an array');
        $this->assertSame(1, count($cacheInfo), 'Cache info contains 1 element');
        $this->assertTrue(!in_array($prefix.'test_1', $cacheInfo), 'Cache info no longer contains the key that matches the pattern (first key)');
        $this->assertTrue(!in_array($prefix.'test_2', $cacheInfo), 'Cache info no longer contains the key that matches the pattern (second key)');
        $this->assertTrue(in_array($prefix.'test3', $cacheInfo), 'Cache info still contains the key that did not match the pattern (third key)');
    }
}
