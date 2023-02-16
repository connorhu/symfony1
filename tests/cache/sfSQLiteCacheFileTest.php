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
class sfSQLiteCacheFileTest extends CacheDriverTestCase
{
    public function setUp(): void
    {
        $database = tempnam(sys_get_temp_dir(), 'file_cache_temp_');
        unlink($database);
        $this->cache = new sfSQLiteCache(array('database' => $database));
    }

    protected function tearDown(): void
    {
        $database = $this->cache->getOption('database');

        if (is_file($database)) {
            unlink($database);
        }
    }
}
