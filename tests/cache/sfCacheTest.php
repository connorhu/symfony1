<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/myCache.php';
require_once __DIR__.'/../fixtures/fakeCache.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfCacheTest extends TestCase
{
    public function testInitialize()
    {
        $cache = new myCache();
        $cache->initialize(array('foo' => 'bar'));
        $this->assertSame('bar', $cache->getOption('foo'), '->initialize() takes an array of options as its first argument');
    }
}
