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
class sfAPCCacheTest extends CacheDriverTestCase
{
    public function setUp(): void
    {
        $this->cache = new sfAPCCache();
        $this->cache->initialize();
    }
}
