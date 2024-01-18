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
class sfEventTest extends TestCase
{
    private $subject;
    private $event;

    public function setUp(): void
    {
        $this->subject = new stdClass();
        $parameters = array('foo' => 'bar');
        $this->event = new sfEvent($this->subject, 'name', $parameters);
    }

    public function testSubject()
    {
        $this->assertSame($this->subject, $this->event->getSubject(), '->getSubject() returns the event subject');
    }

    public function testName()
    {
        $this->assertSame('name', $this->event->getName(), '->getName() returns the event name');
    }

    public function testParameters()
    {
        $this->assertSame(array('foo' => 'bar'), $this->event->getParameters(), '->getParameters() returns the event parameters');
    }

    public function testReturnValue()
    {
        $this->event->setReturnValue('foo');
        $this->assertSame('foo', $this->event->getReturnValue(), '->getReturnValue() returns the return value of the event');
    }

    public function testProcessed()
    {
        $this->event->setProcessed(true);
        $this->assertSame(true, $this->event->isProcessed(), '->isProcessed() returns true if the event has been processed');
        $this->event->setProcessed(false);
        $this->assertSame(false, $this->event->isProcessed(), '->setProcessed() changes the processed status');
    }

    public function testArrayAccessInterface()
    {
        $this->assertSame('bar', $this->event['foo'], 'sfEvent implements the ArrayAccess interface');
        $this->event['foo'] = 'foo';
        $this->assertSame('foo', $this->event['foo'], 'sfEvent implements the ArrayAccess interface');

        $this->assertSame(true, isset($this->event['foo']), 'sfEvent implements the ArrayAccess interface');
        unset($this->event['foo']);
        $this->assertSame(true, !isset($this->event['foo']), 'sfEvent implements the ArrayAccess interface');

        $this->expectException(InvalidArgumentException::class);

        $this->event['foobar'];
    }
}
