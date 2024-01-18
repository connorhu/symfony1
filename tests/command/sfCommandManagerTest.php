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
class sfCommandManagerTest extends TestCase
{
    public function testConstruct()
    {
        $argumentSet = new sfCommandArgumentSet();
        $optionSet = new sfCommandOptionSet();

        $manager = new sfCommandManager();
        $this->assertInstanceOf(sfCommandArgumentSet::class, $manager->getArgumentSet(), '__construct() creates a new sfCommandArgumentsSet if none given');
        $this->assertInstanceOf(sfCommandOptionSet::class, $manager->getOptionSet(), '__construct() creates a new sfCommandOptionSet if none given');

        $manager = new sfCommandManager($argumentSet);
        $this->assertSame($manager->getArgumentSet(), $argumentSet, '__construct() takes a sfCommandArgumentSet as its first argument');
        $this->assertInstanceOf(sfCommandOptionSet::class, $manager->getOptionSet(), '__construct() takes a sfCommandArgumentSet as its first argument');

        $manager = new sfCommandManager($argumentSet, $optionSet);
        $this->assertSame($optionSet, $manager->getOptionSet(), '__construct() can take a sfCommandOptionSet as its second argument');
    }

    public function testSetArgumentSet()
    {
        $manager = new sfCommandManager(new sfCommandArgumentSet());
        $argumentSet = new sfCommandArgumentSet();
        $manager->setArgumentSet($argumentSet);
        $this->assertSame($argumentSet, $manager->getArgumentSet(), '->setArgumentSet() sets the manager argument set');
    }

    public function testSetOptionSet()
    {
        $manager = new sfCommandManager(new sfCommandArgumentSet());
        $optionSet = new sfCommandOptionSet();
        $manager->setOptionSet($optionSet);
        $this->assertSame($manager->getOptionSet(), $optionSet, '->setOptionSet() sets the manager option set');
    }

    /** @dataProvider processDataProvider */
    public function testProcess(sfCommandManager $manager)
    {
        $options = array(
            'foo1' => true,
            'foo2' => true,
            'foo3' => 'default3',
            'foo4' => 'foo4',
            'foo5' => 'foo5',
            'foo6' => 'foo6 foo6',
            'foo7' => 'foo7',
            'foo8' => array('foo', 'bar'),
            'foo9' => 'default9',
            'foo10' => 'foo10',
            'foo11' => 'foo11',
        );
        $arguments = array(
            'foo1' => 'foo1',
            'foo2' => array('foo2', 'foo3', 'foo4'),
        );

        $this->assertSame(true, $manager->isValid(), '->process() processes CLI options');
        $this->assertSame($manager->getOptionValues(), $options, '->process() processes CLI options');
        $this->assertSame($manager->getArgumentValues(), $arguments, '->process() processes CLI options');

        foreach ($options as $name => $value) {
            $this->assertSame($value, $manager->getOptionValue($name), '->getOptionValue() returns the value for the given option name');
        }

        foreach ($arguments as $name => $value) {
            $this->assertSame($value, $manager->getArgumentValue($name), '->getArgumentValue() returns the value for the given argument name');
        }
    }

    /** @dataProvider processDataProvider */
    public function testNonExistantOption(sfCommandManager $manager)
    {
        $this->expectException(sfCommandException::class);
        $manager->getOptionValue('nonexistant');
    }

    /** @dataProvider processDataProvider */
    public function testNonExistantArgument(sfCommandManager $manager)
    {
        $this->expectException(sfCommandException::class);
        $manager->getArgumentValue('nonexistant');
    }

    public function processDataProvider()
    {
        $argumentSet = new sfCommandArgumentSet(array(
            new sfCommandArgument('foo1', sfCommandArgument::REQUIRED),
            new sfCommandArgument('foo2', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY),
        ));
        $optionSet = new sfCommandOptionSet(array(
            new sfCommandOption('foo1', null, sfCommandOption::PARAMETER_NONE),
            new sfCommandOption('foo2', 'f', sfCommandOption::PARAMETER_NONE),
            new sfCommandOption('foo3', null, sfCommandOption::PARAMETER_OPTIONAL, '', 'default3'),
            new sfCommandOption('foo4', null, sfCommandOption::PARAMETER_OPTIONAL, '', 'default4'),
            new sfCommandOption('foo5', null, sfCommandOption::PARAMETER_OPTIONAL, '', 'default5'),
            new sfCommandOption('foo6', 'r', sfCommandOption::PARAMETER_REQUIRED, '', 'default5'),
            new sfCommandOption('foo7', 't', sfCommandOption::PARAMETER_REQUIRED, '', 'default7'),
            new sfCommandOption('foo8', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY),
            new sfCommandOption('foo9', 's', sfCommandOption::PARAMETER_OPTIONAL, '', 'default9'),
            new sfCommandOption('foo10', 'u', sfCommandOption::PARAMETER_OPTIONAL, '', 'default10'),
            new sfCommandOption('foo11', 'v', sfCommandOption::PARAMETER_OPTIONAL, '', 'default11'),
        ));
        $manager = new sfCommandManager($argumentSet, $optionSet);
        $manager->process('--foo1 -f --foo3 --foo4="foo4" --foo5=foo5 -r"foo6 foo6" -t foo7 --foo8="foo" --foo8=bar -s -u foo10 -vfoo11 foo1 foo2 foo3 foo4');

        yield array($manager);
    }

    public function testIsValid()
    {
        $argumentSet = new sfCommandArgumentSet();
        $manager = new sfCommandManager($argumentSet);
        $manager->process('foo');
        $this->assertSame(true, !$manager->isValid(), '->isValid() returns false if the options are not valid');
        $this->assertSame(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

        $argumentSet = new sfCommandArgumentSet(array(new sfCommandArgument('foo', sfCommandArgument::REQUIRED)));
        $manager = new sfCommandManager($argumentSet);
        $manager->process('');
        $this->assertSame(true, !$manager->isValid(), '->isValid() returns false if the options are not valid');
        $this->assertSame(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

        $optionSet = new sfCommandOptionSet(array(new sfCommandOption('foo', null, sfCommandOption::PARAMETER_REQUIRED)));
        $manager = new sfCommandManager(null, $optionSet);
        $manager->process('--foo');
        $this->assertSame(true, !$manager->isValid(), '->isValid() returns false if the options are not valid');
        $this->assertSame(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

        $optionSet = new sfCommandOptionSet(array(new sfCommandOption('foo', 'f', sfCommandOption::PARAMETER_REQUIRED)));
        $manager = new sfCommandManager(null, $optionSet);
        $manager->process('-f');
        $this->assertSame(true, !$manager->isValid(), '->isValid() returns false if the options are not valid');
        $this->assertSame(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

        $optionSet = new sfCommandOptionSet(array(new sfCommandOption('foo', null, sfCommandOption::PARAMETER_NONE)));
        $manager = new sfCommandManager(null, $optionSet);
        $manager->process('--foo="bar"');
        $this->assertSame(true, !$manager->isValid(), '->isValid() returns false if the options are not valid');
        $this->assertSame(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

        $manager = new sfCommandManager();
        $manager->process('--bar');
        $this->assertSame(true, !$manager->isValid(), '->isValid() returns false if the options are not valid');
        $this->assertSame(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

        $manager = new sfCommandManager();
        $manager->process('-b');
        $this->assertSame(true, !$manager->isValid(), '->isValid() returns false if the options are not valid');
        $this->assertSame(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');

        $manager = new sfCommandManager();
        $manager->process('--bar="foo"');
        $this->assertSame(true, !$manager->isValid(), '->isValid() returns false if the options are not valid');
        $this->assertSame(count($manager->getErrors()), 1, '->getErrors() returns an array of errors');
    }
}
