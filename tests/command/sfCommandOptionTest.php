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
class sfCommandOptionTest extends TestCase
{
    public function testConstruct()
    {
        $option = new sfCommandOption('foo');
        $this->assertSame($option->getName(), 'foo', '__construct() takes a name as its first argument');

        $option = new sfCommandOption('--foo');
        $this->assertSame($option->getName(), 'foo', '__construct() removes the leading -- of the option name');

        // shortcut argument
        $option = new sfCommandOption('foo', 'f');
        $this->assertSame($option->getShortcut(), 'f', '__construct() can take a shortcut as its second argument');

        $option = new sfCommandOption('foo', '-f');
        $this->assertSame($option->getShortcut(), 'f', '__construct() removes the leading - of the shortcut');

        // mode argument
        $option = new sfCommandOption('foo', 'f');
        $this->assertSame($option->acceptParameter(), false, '__construct() gives a "sfCommandOption::PARAMETER_NONE" mode by default');
        $this->assertSame($option->isParameterRequired(), false, '__construct() gives a "sfCommandOption::PARAMETER_NONE" mode by default');
        $this->assertSame($option->isParameterOptional(), false, '__construct() gives a "sfCommandOption::PARAMETER_NONE" mode by default');

        $option = new sfCommandOption('foo', 'f', null);
        $this->assertSame($option->acceptParameter(), false, '__construct() can take "sfCommandOption::PARAMETER_NONE" as its mode');
        $this->assertSame($option->isParameterRequired(), false, '__construct() can take "sfCommandOption::PARAMETER_NONE" as its mode');
        $this->assertSame($option->isParameterOptional(), false, '__construct() can take "sfCommandOption::PARAMETER_NONE" as its mode');

        $option = new sfCommandOption('foo', 'f', sfCommandOption::PARAMETER_NONE);
        $this->assertSame($option->acceptParameter(), false, '__construct() can take "sfCommandOption::PARAMETER_NONE" as its mode');
        $this->assertSame($option->isParameterRequired(), false, '__construct() can take "sfCommandOption::PARAMETER_NONE" as its mode');
        $this->assertSame($option->isParameterOptional(), false, '__construct() can take "sfCommandOption::PARAMETER_NONE" as its mode');

        $option = new sfCommandOption('foo', 'f', sfCommandOption::PARAMETER_REQUIRED);
        $this->assertSame($option->acceptParameter(), true, '__construct() can take "sfCommandOption::PARAMETER_REQUIRED" as its mode');
        $this->assertSame($option->isParameterRequired(), true, '__construct() can take "sfCommandOption::PARAMETER_REQUIRED" as its mode');
        $this->assertSame($option->isParameterOptional(), false, '__construct() can take "sfCommandOption::PARAMETER_REQUIRED" as its mode');

        $option = new sfCommandOption('foo', 'f', sfCommandOption::PARAMETER_OPTIONAL);
        $this->assertSame($option->acceptParameter(), true, '__construct() can take "sfCommandOption::PARAMETER_OPTIONAL" as its mode');
        $this->assertSame($option->isParameterRequired(), false, '__construct() can take "sfCommandOption::PARAMETER_OPTIONAL" as its mode');
        $this->assertSame($option->isParameterOptional(), true, '__construct() can take "sfCommandOption::PARAMETER_OPTIONAL" as its mode');
    }

    public function testInvalidMode()
    {
        $this->expectException(sfCommandException::class);

        $option = new sfCommandOption('foo', 'f', 'ANOTHER_ONE');
    }

    public function testIsArray()
    {
        $option = new sfCommandOption('foo', null, sfCommandOption::IS_ARRAY);
        $this->assertSame(true, $option->isArray(), '->isArray() returns true if the option can be an array');
        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_NONE | sfCommandOption::IS_ARRAY);
        $this->assertSame(true, $option->isArray(), '->isArray() returns true if the option can be an array');
        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_NONE);
        $this->assertSame(true, !$option->isArray(), '->isArray() returns false if the option can not be an array');
    }

    public function testGetHelp()
    {
        $option = new sfCommandOption('foo', 'f', null, 'Some help');
        $this->assertSame($option->getHelp(), 'Some help', '->getHelp() returns the help message');
    }

    public function testAcceptParameter()
    {
        $option = new sfCommandOption('foo', null, sfCommandOption::IS_ARRAY);
        $this->assertSame(true, $option->acceptParameter());

        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_OPTIONAL);
        $this->assertSame(true, $option->acceptParameter());

        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_REQUIRED);
        $this->assertSame(true, $option->acceptParameter());

        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_NONE);
        $this->assertSame(false, $option->acceptParameter());
    }

    public function testGetDefault()
    {
        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_OPTIONAL, '', 'default');
        $this->assertSame('default', $option->getDefault(), '->getDefault() returns the default value');

        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_REQUIRED, '', 'default');
        $this->assertSame('default', $option->getDefault(), '->getDefault() returns the default value');

        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_REQUIRED);
        $this->assertSame(true, null === $option->getDefault(), '->getDefault() returns null if no default value is configured');

        $option = new sfCommandOption('foo', null, sfCommandOption::IS_ARRAY);
        $this->assertSame(array(), $option->getDefault(), '->getDefault() returns an empty array if option is an array');

        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_NONE);
        $this->assertSame(true, false === $option->getDefault(), '->getDefault() returns false if the option does not take a parameter');
    }

    public function testSetDefault()
    {
        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_REQUIRED, '', 'default');
        $option->setDefault(null);
        $this->assertSame(true, null === $option->getDefault(), '->setDefault() can reset the default value by passing null');
        $option->setDefault('another');
        $this->assertSame($option->getDefault(), 'another', '->setDefault() changes the default value');

        $option = new sfCommandOption('foo', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY);
        $option->setDefault(array(1, 2));
        $this->assertSame($option->getDefault(), array(1, 2), '->setDefault() changes the default value');
    }

    public function testDefaultValueForNonParameter()
    {
        $this->expectException(sfCommandException::class);

        $option = new sfCommandOption('foo', 'f', sfCommandOption::PARAMETER_NONE);
        $option->setDefault('default');
    }

    public function testNonArrayDefaultValueForArrayOption()
    {
        $this->expectException(sfCommandException::class);

        $option = new sfCommandOption('foo', 'f', sfCommandOption::IS_ARRAY);
        $option->setDefault('default');
    }
}
