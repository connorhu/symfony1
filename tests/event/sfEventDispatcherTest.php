<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/Listener.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfEventDispatcherTest extends TestCase
{
    public function testConnectDisconnect()
    {
        $dispatcher = new sfEventDispatcher();

        $dispatcher->connect('bar', 'listenToBar');
        $this->assertSame(array('listenToBar'), $dispatcher->getListeners('bar'), '->connect() connects a listener to an event name');
        $dispatcher->connect('bar', 'listenToBarBar');
        $this->assertSame(array('listenToBar', 'listenToBarBar'), $dispatcher->getListeners('bar'), '->connect() can connect several listeners for the same event name');

        $dispatcher->connect('barbar', 'listenToBarBar');
        $dispatcher->disconnect('bar', 'listenToBarBar');
        $this->assertSame(array('listenToBar'), $dispatcher->getListeners('bar'), '->disconnect() disconnects a listener for an event name');
        $this->assertSame(array('listenToBarBar'), $dispatcher->getListeners('barbar'), '->disconnect() disconnects a listener for an event name');

        $this->assertSame(true, false === $dispatcher->disconnect('foobar', 'listen'), '->disconnect() returns false if the listener does not exist');
    }

    public function testGetListeners()
    {
        $dispatcher = new sfEventDispatcher();
        $dispatcher->connect('bar', 'listenToBar');

        $this->assertSame(false, $dispatcher->hasListeners('foo'), '->hasListeners() returns false if the event has no listener');
        $dispatcher->connect('foo', 'listenToFoo');
        $this->assertSame(true, $dispatcher->hasListeners('foo'), '->hasListeners() returns true if the event has some listeners');
        $dispatcher->disconnect('foo', 'listenToFoo');
        $this->assertSame(false, $dispatcher->hasListeners('foo'), '->hasListeners() returns false if the event has no listener');

        $this->assertSame(array('listenToBar'), $dispatcher->getListeners('bar'), '->getListeners() returns an array of listeners connected to the given event name');
        $this->assertSame(array(), $dispatcher->getListeners('foobar'), '->getListeners() returns an empty array if no listener are connected to the given event name');
    }

    public function testNotify()
    {
        $listener = new Listener();

        $listener->reset();
        $dispatcher = new sfEventDispatcher();
        $dispatcher->connect('foo', array($listener, 'listenToFoo'));
        $dispatcher->connect('foo', array($listener, 'listenToFooBis'));
        $e = $dispatcher->notify($event = new sfEvent(new stdClass(), 'foo'));
        $this->assertSame('listenToFoolistenToFooBis', $listener->getValue(), '->notify() notifies all registered listeners in order');
        $this->assertSame($event, $e, '->notify() returns the event object');

        $listener->reset();
        $dispatcher = new sfEventDispatcher();
        $dispatcher->connect('foo', array($listener, 'listenToFooBis'));
        $dispatcher->connect('foo', array($listener, 'listenToFoo'));
        $dispatcher->notify(new sfEvent(new stdClass(), 'foo'));
        $this->assertSame('listenToFooBislistenToFoo', $listener->getValue(), '->notify() notifies all registered listeners in order');
    }

    public function testNotifyUntil()
    {
        $listener = new Listener();

        $listener->reset();
        $dispatcher = new sfEventDispatcher();
        $dispatcher->connect('foo', array($listener, 'listenToFoo'));
        $dispatcher->connect('foo', array($listener, 'listenToFooBis'));
        $e = $dispatcher->notifyUntil($event = new sfEvent(new stdClass(), 'foo'));
        $this->assertSame('listenToFoolistenToFooBis', $listener->getValue(), '->notifyUntil() notifies all registered listeners in order and stops if it returns true');
        $this->assertSame($event, $e, '->notifyUntil() returns the event object');

        $listener->reset();
        $dispatcher = new sfEventDispatcher();
        $dispatcher->connect('foo', array($listener, 'listenToFooBis'));
        $dispatcher->connect('foo', array($listener, 'listenToFoo'));
        $e = $dispatcher->notifyUntil($event = new sfEvent(new stdClass(), 'foo'));
        $this->assertSame('listenToFooBis', $listener->getValue(), '->notifyUntil() notifies all registered listeners in order and stops if it returns true');
    }

    public function testFilter()
    {
        $listener = new Listener();

        $listener->reset();
        $dispatcher = new sfEventDispatcher();
        $dispatcher->connect('foo', array($listener, 'filterFoo'));
        $dispatcher->connect('foo', array($listener, 'filterFooBis'));
        $e = $dispatcher->filter($event = new sfEvent(new stdClass(), 'foo'), 'foo');
        $this->assertSame('-*foo*-', $e->getReturnValue(), '->filter() filters a value');
        $this->assertSame($event, $e, '->filter() returns the event object');

        $listener->reset();
        $dispatcher = new sfEventDispatcher();
        $dispatcher->connect('foo', array($listener, 'filterFooBis'));
        $dispatcher->connect('foo', array($listener, 'filterFoo'));
        $e = $dispatcher->filter($event = new sfEvent(new stdClass(), 'foo'), 'foo');
        $this->assertSame('*-foo-*', $e->getReturnValue(), '->filter() filters a value');
    }
}
