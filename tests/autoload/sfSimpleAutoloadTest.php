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
class sfSimpleAutoloadTest extends TestCase
{
    public function testClassLoad()
    {
        $autoload = sfSimpleAutoload::getInstance();
        $autoload->addFile(__DIR__.'/../fixtures/myEventDispatcherTest.php');
        $autoload->register();

        $this->assertSame(true, class_exists('myeventdispatchertest'), '"sfSimpleAutoload" is case insensitive');
    }
}
