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
class sfCommandOptionSetTest extends TestCase
{
    public function testConstruct()
    {
        $foo = new sfCommandOption('foo', 'f');
        $bar = new sfCommandOption('bar', 'b');

        $optionSet = new sfCommandOptionSet();
        $this->assertSame(array(), $optionSet->getOptions(), '__construct() creates a new sfCommandOptionSet object');

        $optionSet = new sfCommandOptionSet(array($foo, $bar));
        $this->assertSame(array('foo' => $foo, 'bar' => $bar), $optionSet->getOptions(), '__construct() takes an array of sfCommandOption objects as its first argument');
    }

    public function testSetOptions()
    {
        $foo = new sfCommandOption('foo', 'f');
        $bar = new sfCommandOption('bar', 'b');

        $optionSet = new sfCommandOptionSet();
        $optionSet->setOptions(array($foo));
        $this->assertSame(array('foo' => $foo), $optionSet->getOptions(), '->setOptions() sets the array of sfCommandOption objects');

        $optionSet->setOptions(array($bar));
        $this->assertSame(array('bar' => $bar), $optionSet->getOptions(), '->setOptions() clears all sfCommandOption objects');

        $this->expectException(sfCommandException::class);
        $optionSet->getOptionForShortcut('f');
    }

    public function testAddOptions()
    {
        $foo = new sfCommandOption('foo', 'f');
        $bar = new sfCommandOption('bar', 'b');

        $optionSet = new sfCommandOptionSet();
        $optionSet->addOptions(array($foo));
        $this->assertSame($optionSet->getOptions(), array('foo' => $foo), '->addOptions() adds an array of sfCommandOption objects');

        $optionSet->addOptions(array($bar));
        $this->assertSame($optionSet->getOptions(), array('foo' => $foo, 'bar' => $bar), '->addOptions() does not clear existing sfCommandOption objects');
    }

    public function testAddOption()
    {
        $foo = new sfCommandOption('foo', 'f');
        $bar = new sfCommandOption('bar', 'b');
        $foo2 = new sfCommandOption('foo', 'p');

        $optionSet = new sfCommandOptionSet();
        $optionSet->addOption($foo);
        $this->assertSame($optionSet->getOptions(), array('foo' => $foo), '->addOption() adds a sfCommandOption object');
        $optionSet->addOption($bar);
        $this->assertSame($optionSet->getOptions(), array('foo' => $foo, 'bar' => $bar), '->addOption() adds a sfCommandOption object');

        $this->expectException(sfCommandException::class);
        $optionSet->addOption($foo2);
    }

    public function testAddOptionWithShortcutAlreadyExists()
    {
        $foo = new sfCommandOption('foo', 'f');
        $foo1 = new sfCommandOption('fooBis', 'f');

        $optionSet = new sfCommandOptionSet();
        $optionSet->addOption($foo);

        $this->expectException(sfCommandException::class);
        $optionSet->addOption($foo1);
    }

    public function testGetOption()
    {
        $foo = new sfCommandOption('foo', 'f');

        $optionSet = new sfCommandOptionSet();
        $optionSet->addOptions(array($foo));
        $this->assertSame($optionSet->getOption('foo'), $foo, '->getOption() returns a sfCommandOption by its name');

        $this->expectException(sfCommandException::class);
        $optionSet->getOption('bar');
    }

    public function testHasOption()
    {
        $foo = new sfCommandOption('foo', 'f');

        $optionSet = new sfCommandOptionSet();
        $optionSet->addOptions(array($foo));
        $this->assertSame($optionSet->hasOption('foo'), true, '->hasOption() returns true if a sfCommandOption exists for the given name');
        $this->assertSame($optionSet->hasOption('bar'), false, '->hasOption() returns false if a sfCommandOption exists for the given name');
    }

    public function testHasShortcut()
    {
        $foo = new sfCommandOption('foo', 'f');

        $optionSet = new sfCommandOptionSet();
        $optionSet->addOptions(array($foo));
        $this->assertSame($optionSet->hasShortcut('f'), true, '->hasShortcut() returns true if a sfCommandOption exists for the given shortcut');
        $this->assertSame($optionSet->hasShortcut('b'), false, '->hasShortcut() returns false if a sfCommandOption exists for the given shortcut');
    }

    public function testGetOptionForShortcut()
    {
        $foo = new sfCommandOption('foo', 'f');

        $optionSet = new sfCommandOptionSet();
        $optionSet->addOptions(array($foo));
        $this->assertSame($optionSet->getOptionForShortcut('f'), $foo, '->getOptionForShortcut() returns a sfCommandOption by its shortcut');

        $this->expectException(sfCommandException::class);
        $optionSet->getOptionForShortcut('l');
    }

    public function testGetDefaults()
    {
        $optionSet = new sfCommandOptionSet();
        $optionSet->addOptions(array(
            new sfCommandOption('foo1', null, sfCommandOption::PARAMETER_NONE),
            new sfCommandOption('foo2', null, sfCommandOption::PARAMETER_REQUIRED),
            new sfCommandOption('foo3', null, sfCommandOption::PARAMETER_REQUIRED, '', 'default'),
            new sfCommandOption('foo4', null, sfCommandOption::PARAMETER_OPTIONAL),
            new sfCommandOption('foo5', null, sfCommandOption::PARAMETER_OPTIONAL, '', 'default'),
            new sfCommandOption('foo6', null, sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY),
            new sfCommandOption('foo7', null, sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY, '', array(1, 2)),
        ));

        $defaults = array(
            'foo1' => false, // TODO that was null
            'foo2' => null,
            'foo3' => 'default',
            'foo4' => null,
            'foo5' => 'default',
            'foo6' => array(),
            'foo7' => array(1, 2),
        );

        $this->assertSame($defaults, $optionSet->getDefaults(), '->getDefaults() returns the default values for all options');
    }
}
