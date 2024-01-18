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
class sfNamespacedParameterHolderTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->clear()
        $this->diag('->clear()');
        $ph = new sfNamespacedParameterHolder();
        $ph->clear();
        $this->is($ph->getAll(), array(), '->clear() clears all parameters');

        $ph->set('foo', 'bar');
        $ph->clear();
        $this->is($ph->getAll(), array(), '->clear() clears all parameters');

        // ->get()
        $this->diag('->get()');
        $ph = new sfNamespacedParameterHolder();
        $ph->set('foo', 'bar');
        $this->is($ph->get('foo'), 'bar', '->get() returns the parameter value for the given key');
        $this->is($ph->get('bar'), null, '->get() returns null if the key does not exist');

        $ph = new sfNamespacedParameterHolder();
        $this->is('default_value', $ph->get('foo1', 'default_value'), '->get() takes the default value as its second argument');

        $ph = new sfNamespacedParameterHolder();
        $ph->set('myfoo', 'bar', 'symfony/mynamespace');
        $this->is('bar', $ph->get('myfoo', null, 'symfony/mynamespace'), '->get() takes an optional namespace as its third argument');
        $this->is(null, $ph->get('myfoo'), '->get() can have the same key for several namespaces');

        // ->getNames()
        $this->diag('->getNames()');
        $ph = new sfNamespacedParameterHolder();
        $ph->set('foo', 'bar');
        $ph->set('yourfoo', 'bar');
        $ph->set('myfoo', 'bar', 'symfony/mynamespace');

        $this->is($ph->getNames(), array('foo', 'yourfoo'), '->getNames() returns all key names for the default namespace');
        $this->is($ph->getNames('symfony/mynamespace'), array('myfoo'), '->getNames() takes a namepace as its first argument');

        // ->getNamespaces()
        $this->diag('->getNamespaces()');
        $ph = new sfNamespacedParameterHolder();
        $ph->set('foo', 'bar');
        $ph->set('yourfoo', 'bar');
        $ph->set('myfoo', 'bar', 'symfony/mynamespace');

        $this->is($ph->getNamespaces(), array($ph->getDefaultNamespace(), 'symfony/mynamespace'), '->getNamespaces() returns all non empty namespaces');

        // ->setDefaultNamespace()
        $this->diag('->setDefaultNamespace()');
        $ph = new sfNamespacedParameterHolder('symfony/mynamespace');
        $ph->setDefaultNamespace('othernamespace');

        $this->is($ph->getDefaultNamespace(), 'othernamespace', '->setDefaultNamespace() sets the default namespace');

        $ph->set('foo', 'bar');
        $ph->setDefaultNamespace('foonamespace');

        $this->is($ph->get('foo'), 'bar', '->setDefaultNamespace() moves values from the old namespace to the new');
        $this->is($ph->get('foo', null, 'othernamespace'), null, '->setDefaultNamespace() moves values from the old namespace to the new');

        $ph->set('foo', 'bar');
        $ph->setDefaultNamespace('barnamespace', false);

        $this->is($ph->get('foo'), null, '->setDefaultNamespace() does not move old values to the new namespace if the second argument is false');
        $this->is($ph->get('foo', null, 'foonamespace'), 'bar', '->setDefaultNamespace() does not move old values to the new namespace if the second argument is false');

        // ->getAll()
        $this->diag('->getAll()');
        $parameters = array('foo' => 'bar', 'myfoo' => 'bar');
        $ph = new sfNamespacedParameterHolder();
        $ph->add($parameters);
        $ph->set('myfoo', 'bar', 'symfony/mynamespace');
        $this->is($ph->getAll(), $parameters, '->getAll() returns all parameters from the default namespace');

        // ->has()
        $this->diag('->has()');
        $ph = new sfNamespacedParameterHolder();
        $ph->set('foo', 'bar');
        $ph->set('myfoo', 'bar', 'symfony/mynamespace');
        $this->is($ph->has('foo'), true, '->has() returns true if the key exists');
        $this->is($ph->has('bar'), false, '->has() returns false if the key does not exist');
        $this->is($ph->has('myfoo'), false, '->has() returns false if the key exists but in another namespace');
        $this->is($ph->has('myfoo', 'symfony/mynamespace'), true, '->has() returns true if the key exists in the namespace given as its second argument');

        // ->hasNamespace()
        $this->diag('->hasNamespace()');
        $ph = new sfNamespacedParameterHolder();
        $ph->set('foo', 'bar');
        $ph->set('myfoo', 'bar', 'symfony/mynamespace');
        $this->is($ph->hasNamespace($ph->getDefaultNamespace()), true, '->hasNamespace() returns true for the default namespace');
        $this->is($ph->hasNamespace('symfony/mynamespace'), true, '->hasNamespace() returns true if the namespace exists');
        $this->is($ph->hasNamespace('symfony/nonexistant'), false, '->hasNamespace() returns false if the namespace does not exist');

        // ->remove()
        $this->diag('->remove()');
        $ph = new sfNamespacedParameterHolder();
        $ph->set('foo', 'bar');
        $ph->set('myfoo', 'bar');
        $ph->set('myfoo', 'bar', 'symfony/mynamespace');

        $ph->remove('foo');
        $this->is($ph->has('foo'), false, '->remove() removes the key from parameters');

        $ph->remove('myfoo');
        $this->is($ph->has('myfoo'), false, '->remove() removes the key from parameters');
        $this->is($ph->has('myfoo', 'symfony/mynamespace'), true, '->remove() removes the key from parameters for a given namespace');

        $ph->remove('myfoo', null, 'symfony/mynamespace');
        $this->is($ph->has('myfoo', 'symfony/mynamespace'), false, '->remove() takes a namespace as its third argument');

        $this->is($ph->remove('nonexistant', 'foobar', 'symfony/mynamespace'), 'foobar', '->remove() takes a default value as its second argument');

        $this->is($ph->getAll(), array(), '->remove() removes the key from parameters');

        // ->removeNamespace()
        $this->diag('->removeNamespace()');
        $ph = new sfNamespacedParameterHolder();
        $ph->set('foo', 'bar');
        $ph->set('myfoo', 'bar');
        $ph->set('myfoo', 'bar', 'symfony/mynamespace');

        $ph->removeNamespace($ph->getDefaultNamespace());
        $this->is($ph->has('foo'), false, '->removeNamespace() removes all keys and values from a namespace');
        $this->is($ph->has('myfoo'), false, '->removeNamespace() removes all keys and values from a namespace');
        $this->is($ph->has('myfoo', 'symfony/mynamespace'), true, '->removeNamespace() does not remove keys in other namepaces');

        $ph->set('foo', 'bar');
        $ph->set('myfoo', 'bar');
        $ph->set('myfoo', 'bar', 'symfony/mynamespace');

        $ph->removeNamespace();
        $this->is($ph->has('foo'), false, '->removeNamespace() removes all keys and values from the default namespace by default');
        $this->is($ph->has('myfoo'), false, '->removeNamespace() removes all keys and values from the default namespace by default');
        $this->is($ph->has('myfoo', 'symfony/mynamespace'), true, '->removeNamespace() does not remove keys in other namepaces');

        $ph->removeNamespace('symfony/mynamespace');
        $this->is($ph->has('myfoo', 'symfony/mynamespace'), false, '->removeNamespace() takes a namespace as its first parameter');

        $this->is($ph->getAll(), array(), '->removeNamespace() removes all the keys from parameters');

        // ->set()
        $this->diag('->set()');
        $foo = 'bar';

        $ph = new sfNamespacedParameterHolder();
        $ph->set('foo', $foo);
        $this->is($ph->get('foo'), $foo, '->set() sets the value for a key');

        $foo = 'foo';
        $this->is($ph->get('foo'), 'bar', '->set() sets the value for a key, not a reference');

        $ph->set('myfoo', 'bar', 'symfony/mynamespace');
        $this->is($ph->get('myfoo', null, 'symfony/mynamespace'), 'bar', '->set() takes a namespace as its third parameter');

        // ->setByRef()
        $this->diag('->setByRef()');
        $foo = 'bar';

        $ph = new sfNamespacedParameterHolder();
        $ph->setByRef('foo', $foo);
        $this->is($ph->get('foo'), $foo, '->setByRef() sets the value for a key');

        $foo = 'foo';
        $this->is($ph->get('foo'), $foo, '->setByRef() sets the value for a key as a reference');

        $myfoo = 'bar';
        $ph->setByRef('myfoo', $myfoo, 'symfony/mynamespace');
        $this->is($ph->get('myfoo', null, 'symfony/mynamespace'), $myfoo, '->setByRef() takes a namespace as its third parameter');

        // ->add()
        $this->diag('->add()');
        $foo = 'bar';
        $parameters = array('foo' => $foo, 'bar' => 'bar');
        $myparameters = array('myfoo' => 'bar', 'mybar' => 'bar');

        $ph = new sfNamespacedParameterHolder();
        $ph->add($parameters);
        $ph->add($myparameters, 'symfony/mynamespace');

        $this->is($ph->getAll(), $parameters, '->add() adds an array of parameters');
        $this->is($ph->getAll('symfony/mynamespace'), $myparameters, '->add() takes a namespace as its second argument');

        $foo = 'mybar';
        $this->is($ph->getAll(), $parameters, '->add() adds an array of parameters, not a reference');

        // ->addByRef()
        $this->diag('->addByRef()');
        $foo = 'bar';
        $parameters = array('foo' => &$foo, 'bar' => 'bar');
        $myparameters = array('myfoo' => 'bar', 'mybar' => 'bar');

        $ph = new sfNamespacedParameterHolder();
        $ph->addByRef($parameters);
        $ph->addByRef($myparameters, 'symfony/mynamespace');

        $this->is($parameters, $ph->getAll(), '->add() adds an array of parameters');
        $this->is($myparameters, $ph->getAll('symfony/mynamespace'), '->add() takes a namespace as its second argument');

        $foo = 'mybar';
        $this->is($parameters, $ph->getAll(), '->add() adds a reference of an array of parameters');

        // ->serialize() ->unserialize()
        $this->diag('->serialize() ->unserialize()');
        $this->ok($ph == unserialize(serialize($ph)), 'sfNamespacedParameterHolder implements the Serializable interface');
    }
}
