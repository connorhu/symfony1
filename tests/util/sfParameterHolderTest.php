<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../PhpUnitSfTestHelperTrait.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfParameterHolderTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->clear()
        $this->diag('->clear()');
        $ph = new sfParameterHolder();
        $ph->clear();
        $this->is($ph->getAll(), array(), '->clear() clears all parameters');

        $ph->set('foo', 'bar');
        $ph->clear();
        $this->is($ph->getAll(), array(), '->clear() clears all parameters');

        // ->get()
        $this->diag('->get()');
        $ph = new sfParameterHolder();
        $ph->set('foo', 'bar');
        $this->is($ph->get('foo'), 'bar', '->get() returns the parameter value for the given key');
        $this->is($ph->get('bar'), null, '->get() returns null if the key does not exist');

        // checks that get returns reference
        $ph->set('ref', 'foobar');

        $ref1 = null;
        $ref1 = &$ph->get('ref');
        $this->is($ref1, 'foobar');

        $ref2 = null;
        $ref2 = &$ph->get('ref'); // obtain the very same reference and modify it
        $ref2 = 'barfoo';

        $this->is($ref1, 'barfoo');
        $this->is($ref2, 'barfoo');
        $this->is($ph->get('ref'), 'barfoo');
        $this->is($ref2, $ref1, '->get() returns a reference for the given key');

        $ph = new sfParameterHolder();
        $this->is('default_value', $ph->get('foo1', 'default_value'), '->get() takes the default value as its second argument');

        // ->getNames()
        $this->diag('->getNames()');
        $ph = new sfParameterHolder();
        $ph->set('foo', 'bar');
        $ph->set('yourfoo', 'bar');

        $this->is($ph->getNames(), array('foo', 'yourfoo'), '->getNames() returns all key names');

        // ->getAll()
        $this->diag('->getAll()');
        $parameters = array('foo' => 'bar', 'myfoo' => 'bar');
        $ph = new sfParameterHolder();
        $ph->add($parameters);
        $this->is($ph->getAll(), $parameters, '->getAll() returns all parameters');

        // ->has()
        $this->diag('->has()');
        $ph = new sfParameterHolder();
        $ph->set('foo', 'bar');
        $this->is($ph->has('foo'), true, '->has() returns true if the key exists');
        $this->is($ph->has('bar'), false, '->has() returns false if the key does not exist');
        $ph->set('bar', null);
        $this->is($ph->has('bar'), true, '->has() returns true if the key exist, even if the value is null');

        // ->remove()
        $this->diag('->remove()');
        $ph = new sfParameterHolder();
        $ph->set('foo', 'bar');
        $ph->set('myfoo', 'bar');

        $ph->remove('foo');
        $this->is($ph->has('foo'), false, '->remove() removes the key from parameters');

        $ph->remove('myfoo');
        $this->is($ph->has('myfoo'), false, '->remove() removes the key from parameters');

        $this->is($ph->remove('nonexistant', 'foobar'), 'foobar', '->remove() takes a default value as its second argument');

        $this->is($ph->getAll(), array(), '->remove() removes the key from parameters');

        // ->set()
        $this->diag('->set()');
        $foo = 'bar';

        $ph = new sfParameterHolder();
        $ph->set('foo', $foo);
        $this->is($ph->get('foo'), $foo, '->set() sets the value for a key');

        $foo = 'foo';
        $this->is($ph->get('foo'), 'bar', '->set() sets the value for a key, not a reference');

        // ->setByRef()
        $this->diag('->setByRef()');
        $foo = 'bar';

        $ph = new sfParameterHolder();
        $ph->setByRef('foo', $foo);
        $this->is($ph->get('foo'), $foo, '->setByRef() sets the value for a key');

        $foo = 'foo';
        $this->is($ph->get('foo'), $foo, '->setByRef() sets the value for a key as a reference');

        // ->add()
        $this->diag('->add()');
        $foo = 'bar';
        $parameters = array('foo' => $foo, 'bar' => 'bar');
        $myparameters = array('myfoo' => 'bar', 'mybar' => 'bar');

        $ph = new sfParameterHolder();
        $ph->add($parameters);

        $this->is($ph->getAll(), $parameters, '->add() adds an array of parameters');

        $foo = 'mybar';
        $this->is($ph->getAll(), $parameters, '->add() adds an array of parameters, not a reference');

        // ->addByRef()
        $this->diag('->addByRef()');
        $foo = 'bar';
        $parameters = array('foo' => &$foo, 'bar' => 'bar');
        $myparameters = array('myfoo' => 'bar', 'mybar' => 'bar');

        $ph = new sfParameterHolder();
        $ph->addByRef($parameters);

        $this->is($parameters, $ph->getAll(), '->add() adds an array of parameters');

        $foo = 'mybar';
        $this->is($parameters, $ph->getAll(), '->add() adds a reference of an array of parameters');

        // ->serialize() ->unserialize()
        $this->diag('->serialize() ->unserialize()');
        $this->ok($ph == unserialize(serialize($ph)), 'sfParameterHolder implements the Serializable interface');
    }
}
