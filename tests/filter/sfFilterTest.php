<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../sfParameterHolderProxyTestCase.php';
require_once __DIR__.'/../fixtures/myFilter.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfFilterTest extends sfParameterHolderProxyTestCase
{
    private sfContext $context;
    private $filter;

    protected function setUp(): void
    {
        $this->methodName = 'parameter';

        $this->context = sfContext::getInstance();
        $this->object = $this->filter = new myFilter($this->context);
    }

    public function testInitialize()
    {
        $this->assertSame($this->context, $this->filter->getContext(), '->initialize() takes a sfContext object as its first argument');
        $this->filter->initialize($this->context, array('foo' => 'bar'));
        $this->assertSame('bar', $this->filter->getParameter('foo'), '->initialize() takes an array of parameters as its second argument');
    }

    public function testContext()
    {
        $this->filter->initialize($this->context);
        $this->assertSame($this->filter->getContext(), $this->context, '->getContext() returns the current context');
    }

    public function testFirstCall()
    {
        $filter = new myFilter($this->context);

        $this->assertSame(true, $filter->isFirstCall('beforeExecution'), '->isFirstCall() returns true if this is the first call with this argument');
        $this->assertSame(false, $filter->isFirstCall('beforeExecution'), '->isFirstCall() returns false if this is not the first call with this argument');
        $this->assertSame(false, $filter->isFirstCall('beforeExecution'), '->isFirstCall() returns false if this is not the first call with this argument');

        $filter = new myFilter($this->context);
        $filter->initialize($this->context);
        $this->assertSame(false, $filter->isFirstCall('beforeExecution'), '->isFirstCall() returns false if this is not the first call with this argument');
    }
}
