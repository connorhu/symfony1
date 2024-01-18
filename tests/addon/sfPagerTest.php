<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/myPager.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfPagerTest extends TestCase
{
    /**
     * #8021.
     */
    public function testRewind()
    {
        $pager = new myPager('fooClass');

        $countRuns = 0;
        foreach ($pager as $item) {
            ++$countRuns;
        }
        $this->assertSame($pager->count(), $countRuns, 'iterating first time will invoke on all items');

        $countRuns = 0;
        $pager->rewind();
        foreach ($pager as $item) {
            ++$countRuns;
        }
        $this->assertSame($pager->count(), $countRuns, '->rewind will allow reiterating');
    }
}
