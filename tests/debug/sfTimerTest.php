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
class sfTimerTest extends TestCase
{
    public function testStartingAndStopping()
    {
        $timer = new sfTimer();
        $timer->addTime();
        sleep(1);
        $timer->addTime();
        $this->assertSame(2, $timer->getCalls(), '->getCalls() returns the amount of addTime() calls');
        $this->assertSame(true, $timer->getElapsedTime() > 0, '->getElapsedTime() returns a value greater than zero. No precision is tested by the unit test to avoid false alarms');
    }

    public function testTimeManager()
    {
        $timerA = sfTimerManager::getTimer('timerA');
        $timerB = sfTimerManager::getTimer('timerB');
        $this->assertInstanceOf('sfTimer', $timerA, '::getTimer() returns an sfTimer instance');

        $timers = sfTimerManager::getTimers();
        $this->assertSame(2, \count($timers), '::getTimers() returns an array with the timers created by the timer manager');
        $this->assertSame($timerA, $timers['timerA'], '::getTimers() returns an array with keys being the timer name');
        sfTimerManager::clearTimers();
        $this->assertSame(0, \count(sfTimerManager::getTimers()), '::clearTimers() empties the list of the timer instances');
    }
}
