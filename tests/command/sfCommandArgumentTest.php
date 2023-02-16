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
class sfCommandArgumentTest extends TestCase
{
    public function testConstruct()
    {
        $argument = new sfCommandArgument('foo');
        $this->assertSame('foo', $argument->getName(), '__construct() takes a name as its first argument');

        // mode argument
        $argument = new sfCommandArgument('foo');
        $this->assertSame(false, $argument->isRequired(), '__construct() gives a "sfCommandArgument::OPTIONAL" mode by default');

        $argument = new sfCommandArgument('foo', null);
        $this->assertSame(false, $argument->isRequired(), '__construct() can take "sfCommandArgument::OPTIONAL" as its mode');

        $argument = new sfCommandArgument('foo', sfCommandArgument::OPTIONAL);
        $this->assertSame(false, $argument->isRequired(), '__construct() can take "sfCommandArgument::PARAMETER_OPTIONAL" as its mode');

        $argument = new sfCommandArgument('foo', sfCommandArgument::REQUIRED);
        $this->assertSame(true, $argument->isRequired(), '__construct() can take "sfCommandArgument::PARAMETER_REQUIRED" as its mode');

        $this->expectException(sfCommandException::class);
        $argument = new sfCommandArgument('foo', 'ANOTHER_ONE');
    }

    public function testIsArray()
    {
        $argument = new sfCommandArgument('foo', sfCommandArgument::IS_ARRAY);
        $this->assertSame(true, $argument->isArray(), '->isArray() returns true if the argument can be an array');

        $argument = new sfCommandArgument('foo', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY);
        $this->assertSame(true, $argument->isArray(), '->isArray() returns true if the argument can be an array');

        $argument = new sfCommandArgument('foo', sfCommandArgument::OPTIONAL);
        $this->assertSame(true, !$argument->isArray(), '->isArray() returns false if the argument can not be an array');
    }

    public function testGetHelp()
    {
        $argument = new sfCommandArgument('foo', null, 'Some help');
        $this->assertSame('Some help', $argument->getHelp(), '->getHelp() return the message help');
    }

    public function testGetDefault()
    {
        $argument = new sfCommandArgument('foo', sfCommandArgument::OPTIONAL, '', 'default');
        $this->assertSame('default', $argument->getDefault(), '->getDefault() return the default value');
    }

    public function testSetDefault()
    {
        $argument = new sfCommandArgument('foo', sfCommandArgument::OPTIONAL, '', 'default');
        $argument->setDefault(null);
        $this->assertSame(true, null === $argument->getDefault(), '->setDefault() can reset the default value by passing null');

        $argument->setDefault('another');
        $this->assertSame($argument->getDefault(), 'another', '->setDefault() changes the default value');

        $argument = new sfCommandArgument('foo', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY);
        $argument->setDefault(array(1, 2));
        $this->assertSame(array(1, 2), $argument->getDefault(), '->setDefault() changes the default value');
    }

    public function testDefaultValueForRequired()
    {
        $this->expectException(sfCommandException::class);

        $argument = new sfCommandArgument('foo', sfCommandArgument::REQUIRED);
        $argument->setDefault('default');
    }

    public function testNonArrayForArrayArgument()
    {
        $this->expectException(sfCommandException::class);

        $argument = new sfCommandArgument('foo', sfCommandArgument::IS_ARRAY);
        $argument->setDefault('default');
    }
}
