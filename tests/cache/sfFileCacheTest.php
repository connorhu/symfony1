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
class sfFileCacheTest extends CacheDriverTestCase
{
    public function setUp(): void
    {
        sfConfig::set('sf_logging_enabled', false);

        $temp = tempnam(sys_get_temp_dir(), 'file_cache_temp_');
        unlink($temp);
        mkdir($temp);

        $this->cache = new sfFileCache(array(
            'cache_dir' => $temp,
        ));
    }

    protected function tearDown(): void
    {
        $cacheDir = $this->cache->getOption('cache_dir');

        sfToolkit::clearDirectory($cacheDir);
        rmdir($cacheDir);
    }

    public function testCacheDirOptionMissing()
    {
        $this->expectException(\sfInitializationException::class);

        new sfFileCache();
    }
}
