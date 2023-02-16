<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/A.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfDebugTest extends TestCase
{
    public function testRemoveObjects()
    {
        $objectArray = array('foo', 42, new sfDebug(), array('bar', 23, new A()));
        $cleanedArray = array('foo', 42, 'sfDebug Object()', array('bar', 23, 'A Object()'));

        $this->assertSame($cleanedArray, sfDebug::removeObjects($objectArray), '::removeObjects() converts objects to String representations using the class name');
    }
}
