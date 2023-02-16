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
class sfConfigTest extends TestCase
{
    public function setUp(): void
    {
        sfConfig::clear();
    }

    public function testSet()
    {
        sfConfig::set('foo', 'bar');

        $this->assertSame('bar', sfConfig::get('foo'));
        $this->assertSame('default_value', sfConfig::get('foo1', 'default_value'));
    }

    public function testHas()
    {
        $this->assertSame(false, sfConfig::has('foo'));

        sfConfig::set('foo', 'bar');

        $this->assertSame(true, sfConfig::has('foo'));
    }

    public function testAdd()
    {
        sfConfig::set('foo', 'bar');
        sfConfig::set('foo1', 'foo1');
        sfConfig::add(array('foo' => 'foo', 'bar' => 'bar'));

        $this->assertSame('foo', sfConfig::get('foo'));
        $this->assertSame('bar', sfConfig::get('bar'));
        $this->assertSame('foo1', sfConfig::get('foo1'));
    }

    public function testGetAll()
    {
        sfConfig::set('foo', 'bar');
        sfConfig::set('foo1', 'foo1');

        $this->assertSame(array('foo' => 'bar', 'foo1' => 'foo1'), sfConfig::getAll());
    }

    public function testClear()
    {
        sfConfig::set('foo1', 'foo1');
        $this->assertSame('foo1', sfConfig::get('foo1'));
        sfConfig::clear();
        $this->assertSame(null, sfConfig::get('foo1'));
    }
}
