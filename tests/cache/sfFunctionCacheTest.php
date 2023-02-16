<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/sfSimpleCache.php';
require_once __DIR__.'/../fixtures/testFunctionCache.php';
require_once __DIR__.'/../fixtures/testRandomFunctionCache.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfFunctionCacheTest extends TestCase
{
    public function testFunction()
    {
        $cache = new sfSimpleCache();
        $functionCache = new sfFunctionCache($cache);

        $result = testFunctionCache(1, 2);

        $this->assertSame('121', $result);
        $this->assertSame('122', $functionCache->call('testFunctionCache', array(1, 2)), $result, '->call() works with functions');
        $this->assertSame('122', $functionCache->call('testFunctionCache', array(1, 2)), $result, '->call() stores the function call in cache');
    }

    public function testClassStaticMethod()
    {
        $cache = new sfSimpleCache();
        $functionCache = new sfFunctionCache($cache);

        $result = testFunctionCache::testStatic(1, 2);

        $this->assertSame(1, testFunctionCache::$count);
        $this->assertSame($result, $functionCache->call(array(testFunctionCache::class, 'testStatic'), array(1, 2)), '->call() works with static method calls');
        $this->assertSame(2, testFunctionCache::$count);
        $this->assertSame($result, $functionCache->call(array(testFunctionCache::class, 'testStatic'), array(1, 2)), '->call() stores the function call in cache');
        $this->assertSame(2, testFunctionCache::$count);
    }

    public function testClassNonStaticMethod()
    {
        $cache = new sfSimpleCache();
        $functionCache = new sfFunctionCache($cache);

        testFunctionCache::$count = 0;

        $object = new testFunctionCache();
        $result = $object->test(1, 2);

        $this->assertSame(1, testFunctionCache::$count);
        $this->assertSame($result, $functionCache->call(array($object, 'test'), array(1, 2)), '->call() works with object methods');
        $this->assertSame(2, testFunctionCache::$count);
        $this->assertSame($result, $functionCache->call(array($object, 'test'), array(1, 2)), '->call() stores the function call in cache');
        $this->assertSame(2, testFunctionCache::$count);
    }
}
