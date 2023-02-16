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
class sfCommandArgumentSetTest extends TestCase
{
    public function testConstruct()
    {
        $argumentSet = new sfCommandArgumentSet();

        $this->assertSame(array(), $argumentSet->getArguments(), '__construct() creates a new sfCommandArgumentSet object');

        $foo = new sfCommandArgument('foo');
        $bar = new sfCommandArgument('bar');
        $argumentSet = new sfCommandArgumentSet(array($foo, $bar));
        $this->assertSame(array('foo' => $foo, 'bar' => $bar), $argumentSet->getArguments(), '__construct() takes an array of sfCommandArgument objects as its first argument');
    }

    public function testSetArguments()
    {
        $foo = new sfCommandArgument('foo');
        $bar = new sfCommandArgument('bar');

        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->setArguments(array($foo));
        $this->assertSame(array('foo' => $foo), $argumentSet->getArguments(), '->setArguments() sets the array of sfCommandArgument objects');

        $argumentSet->setArguments(array($bar));
        $this->assertSame(array('bar' => $bar), $argumentSet->getArguments(), '->setArguments() clears all sfCommandArgument objects');
    }

    public function testAddArgument()
    {
        $foo = new sfCommandArgument('foo');
        $bar = new sfCommandArgument('bar');

        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->addArguments(array($foo));
        $this->assertSame(array('foo' => $foo), $argumentSet->getArguments(), '->addArguments() adds an array of sfCommandArgument objects');

        $argumentSet->addArguments(array($bar));
        $this->assertSame(array('foo' => $foo, 'bar' => $bar), $argumentSet->getArguments(), '->addArguments() does not clear existing sfCommandArgument objects');
    }

    public function testConflictNames()
    {
        $foo = new sfCommandArgument('foo');
        $foo2 = new sfCommandArgument('foo');

        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->addArguments(array($foo));

        $this->expectException(sfCommandException::class);
        $argumentSet->addArgument($foo2);
    }

    public function testArgumentAfterArray()
    {
        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->addArgument(new sfCommandArgument('fooarray', sfCommandArgument::IS_ARRAY));

        $this->expectException(sfCommandException::class);
        $argumentSet->addArgument(new sfCommandArgument('anotherbar'));
    }

    public function testRequiredAfterOptional()
    {
        $foo = new sfCommandArgument('foo');
        $foo2 = new sfCommandArgument('foo2', sfCommandArgument::REQUIRED);

        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->addArgument($foo);

        $this->expectException(sfCommandException::class);
        $argumentSet->addArgument($foo2);
    }

    public function testGetArgument()
    {
        $foo = new sfCommandArgument('foo');

        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->addArguments(array($foo));

        $this->assertSame($foo, $argumentSet->getArgument('foo'), '->getArgument() returns a sfCommandArgument by its name');

        $this->expectException(sfCommandException::class);
        $argumentSet->getArgument('bar');
    }

    public function testHasArgument()
    {
        $foo = new sfCommandArgument('foo');

        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->addArguments(array($foo));
        $this->assertSame($argumentSet->hasArgument('foo'), true, '->hasArgument() returns true if a sfCommandArgument exists for the given name');
        $this->assertSame($argumentSet->hasArgument('bar'), false, '->hasArgument() returns false if a sfCommandArgument exists for the given name');
    }

    public function testGetArgumentRequiredCount()
    {
        $foo2 = new sfCommandArgument('foo2', sfCommandArgument::REQUIRED);
        $foo = new sfCommandArgument('foo');

        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->addArgument($foo2);
        $this->assertSame($argumentSet->getArgumentRequiredCount(), 1, '->getArgumentRequiredCount() returns the number of required arguments');

        $argumentSet->addArgument($foo);
        $this->assertSame($argumentSet->getArgumentRequiredCount(), 1, '->getArgumentRequiredCount() returns the number of required arguments');
    }

    public function testGetArgumentCount()
    {
        $foo2 = new sfCommandArgument('foo2', sfCommandArgument::REQUIRED);
        $foo = new sfCommandArgument('foo');

        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->addArgument($foo2);
        $this->assertSame($argumentSet->getArgumentCount(), 1, '->getArgumentCount() returns the number of arguments');

        $argumentSet->addArgument($foo);
        $this->assertSame($argumentSet->getArgumentCount(), 2, '->getArgumentCount() returns the number of arguments');
    }

    public function testGetDefaults()
    {
        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->addArguments(array(
            new sfCommandArgument('foo1', sfCommandArgument::OPTIONAL),
            new sfCommandArgument('foo2', sfCommandArgument::OPTIONAL, '', 'default'),
            new sfCommandArgument('foo3', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY),
            //  new sfCommandArgument('foo4', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY, '', array(1, 2)),
        ));
        $this->assertSame($argumentSet->getDefaults(), array('foo1' => null, 'foo2' => 'default', 'foo3' => array()), '->getDefaults() return the default values for each argument');

        $argumentSet = new sfCommandArgumentSet();
        $argumentSet->addArguments(array(
            new sfCommandArgument('foo4', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY, '', array(1, 2)),
        ));
        $this->assertSame($argumentSet->getDefaults(), array('foo4' => array(1, 2)), '->getDefaults() return the default values for each argument');
    }
}
