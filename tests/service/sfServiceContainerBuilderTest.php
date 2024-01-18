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
require_once __DIR__.'/../fixtures/service/includes/foo.php';
require_once __DIR__.'/../fixtures/service/includes/classes.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfServiceContainerBuilderTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->setServiceDefinitions() ->addServiceDefinitions() ->getServiceDefinitions() ->setServiceDefinition() ->getServiceDefinition() ->hasServiceDefinition()
        $this->diag('->setServiceDefinitions() ->addServiceDefinitions() ->getServiceDefinitions() ->setServiceDefinition() ->getServiceDefinition() ->hasServiceDefinition()');
        $builder = new sfServiceContainerBuilder();
        $definitions = array(
            'foo' => new sfServiceDefinition('FooClass'),
            'bar' => new sfServiceDefinition('BarClass'),
        );
        $builder->setServiceDefinitions($definitions);
        $this->is($builder->getServiceDefinitions(), $definitions, '->setServiceDefinitions() sets the service definitions');
        $this->ok($builder->hasServiceDefinition('foo'), '->hasServiceDefinition() returns true if a service definition exists');
        $this->ok(!$builder->hasServiceDefinition('foobar'), '->hasServiceDefinition() returns false if a service definition does not exist');

        $builder->setServiceDefinition('foobar', $foo = new sfServiceDefinition('FooBarClass'));
        $this->is($builder->getServiceDefinition('foobar'), $foo, '->getServiceDefinition() returns a service definition if defined');
        $this->ok($builder->setServiceDefinition('foobar', $foo = new sfServiceDefinition('FooBarClass')) === $foo, '->setServiceDefinition() implements a fuild interface by returning the service reference');

        $builder->addServiceDefinitions($defs = array('foobar' => new sfServiceDefinition('FooBarClass')));
        $this->is($builder->getServiceDefinitions(), array_merge($definitions, $defs), '->addServiceDefinitions() adds the service definitions');

        try {
            $builder->getServiceDefinition('baz');
            $this->fail('->getServiceDefinition() throws an InvalidArgumentException if the service definition does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->getServiceDefinition() throws an InvalidArgumentException if the service definition does not exist');
        }

        // ->register()
        $this->diag('->register()');
        $builder = new sfServiceContainerBuilder();
        $builder->register('foo', 'FooClass');
        $this->ok($builder->hasServiceDefinition('foo'), '->register() registers a new service definition');
        $this->ok($builder->getServiceDefinition('foo') instanceof sfServiceDefinition, '->register() returns the newly created sfServiceDefinition instance');

        // ->setAlias()
        $this->diag('->setAlias()');
        $builder = new sfServiceContainerBuilder();
        $builder->register('foo', 'stdClass');
        $builder->setAlias('bar', 'foo');
        $this->ok($builder->hasService('bar'), '->setAlias() defines a new service');
        $this->ok($builder->getService('bar') === $builder->getService('foo'), '->setAlias() creates a service that is an alias to another one');

        // ->getAliases()
        $this->diag('->getAliases()');
        $builder = new sfServiceContainerBuilder();
        $builder->setAlias('bar', 'foo');
        $builder->setAlias('foobar', 'foo');
        $this->is($builder->getAliases(), array('bar' => 'foo', 'foobar' => 'foo'), '->getAliases() returns all service aliases');
        $builder->register('bar', 'stdClass');
        $this->is($builder->getAliases(), array('foobar' => 'foo'), '->getAliases() does not return aliased services that have been overridden');
        $builder->setService('foobar', 'stdClass');
        $this->is($builder->getAliases(), array(), '->getAliases() does not return aliased services that have been overridden');

        // ->hasService()
        $this->diag('->hasService()');
        $builder = new sfServiceContainerBuilder();
        $this->ok(!$builder->hasService('foo'), '->hasService() returns false if the service does not exist');
        $builder->register('foo', 'FooClass');
        $this->ok($builder->hasService('foo'), '->hasService() returns true if a service definition exists');
        $builder->setAlias('foobar', 'foo');
        $this->ok($builder->hasService('foo'), '->hasService() returns true if a service definition exists');
        $builder->setService('bar', new stdClass());
        $this->ok($builder->hasService('bar'), '->hasService() returns true if a service exists');
        $builder->setAlias('foobar2', 'foo');
        $this->ok($builder->hasService('foobar2'), '->hasService() returns true if a service exists');

        // ->getService()
        $this->diag('->getService()');
        $builder = new sfServiceContainerBuilder();
        try {
            $builder->getService('foo');
            $this->fail('->getService() throws an InvalidArgumentException if the service does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->getService() throws an InvalidArgumentException if the service does not exist');
        }
        $builder->register('foo', 'stdClass');
        $this->ok(is_object($builder->getService('foo')), '->getService() returns the service definition associated with the id');
        $builder->setService('bar', $bar = new stdClass());
        $this->is($builder->getService('bar'), $bar, '->getService() returns the service associated with the id');
        $builder->register('bar', 'stdClass');
        $this->is($builder->getService('bar'), $bar, '->getService() returns the service associated with the id even if a definition has been defined');

        $builder->register('baz', 'stdClass')->setArguments(array(new sfServiceReference('baz')));
        try {
            @$builder->getService('baz');
            $this->fail('->getService() throws a LogicException if the service has a circular reference to itself');
        } catch (LogicException $e) {
            $this->pass('->getService() throws a LogicException if the service has a circular reference to itself');
        }

        $builder->register('foobar', 'stdClass')->setShared(true);
        $this->ok($builder->getService('bar') === $builder->getService('bar'), '->getService() always returns the same instance if the service is shared');

        // ->getServiceIds()
        $this->diag('->getServiceIds()');
        $builder = new sfServiceContainerBuilder();
        $builder->register('foo', 'stdClass');
        $builder->register('bar', 'stdClass');
        $this->is($builder->getServiceIds(), array('foo', 'bar', 'service_container'), '->getServiceIds() returns all defined service ids');

        // ->createService() # file
        $this->diag('->createService() # file');
        $builder = new sfServiceContainerBuilder();
        $builder->register('foo1', 'FooClass')->setFile(__DIR__.'/../fixtures/service/includes/foo.php');
        $this->ok($builder->getService('foo1') instanceof FooClass, '->createService() requires the file defined by the service definition');
        $builder->register('foo2', 'FooClass')->setFile(__DIR__.'/../fixtures/service/includes/%file%.php');
        $builder->setParameter('file', 'foo');
        $this->ok($builder->getService('foo2') instanceof FooClass, '->createService() replaces parameters in the file provided by the service definition');

        // ->createService() # class
        $this->diag('->createService() # class');
        $builder = new sfServiceContainerBuilder();
        $builder->register('foo1', '%class%');
        $builder->setParameter('class', 'stdClass');
        $this->ok($builder->getService('foo1') instanceof stdClass, '->createService() replaces parameters in the class provided by the service definition');

        // ->createService() # arguments
        $this->diag('->createService() # arguments');
        $builder = new sfServiceContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'FooClass')->addArgument(array('foo' => '%value%', '%value%' => 'foo', new sfServiceReference('bar')));
        $builder->setParameter('value', 'bar');
        $this->is($builder->getService('foo1')->arguments, array('foo' => 'bar', 'bar' => 'foo', $builder->getService('bar')), '->createService() replaces parameters and service references in the arguments provided by the service definition');

        // ->createService() # constructor
        $this->diag('->createService() # constructor');
        $builder = new sfServiceContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'FooClass')->setConstructor('getInstance')->addArgument(array('foo' => '%value%', '%value%' => 'foo', new sfServiceReference('bar')));
        $builder->setParameter('value', 'bar');
        $this->ok($builder->getService('foo1')->called, '->createService() calls the constructor to create the service instance');
        $this->is($builder->getService('foo1')->arguments, array('foo' => 'bar', 'bar' => 'foo', $builder->getService('bar')), '->createService() passes the arguments to the constructor');

        // ->createService() # method calls
        $this->diag('->createService() # method calls');
        $builder = new sfServiceContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'FooClass')->addMethodCall('setBar', array(array('%value%', new sfServiceReference('bar'))));
        $builder->setParameter('value', 'bar');
        $this->is($builder->getService('foo1')->bar, array('bar', $builder->getService('bar')), '->createService() replaces the values in the method calls arguments');

        // ->createService() # configurator
        $this->diag('->createService() # configurator');
        $builder = new sfServiceContainerBuilder();
        $builder->register('foo1', 'FooClass')->setConfigurator('sc_configure');
        $this->ok($builder->getService('foo1')->configured, '->createService() calls the configurator');

        $builder->register('foo2', 'FooClass')->setConfigurator(array('%class%', 'configureStatic'));
        $builder->setParameter('class', 'BazClass');
        $this->ok($builder->getService('foo2')->configured, '->createService() calls the configurator');

        $builder->register('baz', 'BazClass');
        $builder->register('foo3', 'FooClass')->setConfigurator(array(new sfServiceReference('baz'), 'configure'));
        $this->ok($builder->getService('foo3')->configured, '->createService() calls the configurator');

        $builder->register('foo4', 'FooClass')->setConfigurator('foo');
        try {
            $builder->getService('foo4');
            $this->fail('->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
        } catch (InvalidArgumentException $e) {
            $this->pass('->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
        }

        // ->resolveValue()
        $this->diag('->resolveValue()');
        $builder = new sfServiceContainerBuilder();
        $this->is($builder->resolveValue('foo'), 'foo', '->resolveValue() returns its argument unmodified if no placeholders are found');
        $builder->setParameter('foo', 'bar');
        $this->is($builder->resolveValue('I\'m a %foo%'), 'I\'m a bar', '->resolveValue() replaces placeholders by their values');
        $builder->setParameter('foo', true);
        $this->ok(true === $builder->resolveValue('%foo%'), '->resolveValue() replaces arguments that are just a placeholder by their value without casting them to strings');

        $builder->setParameter('foo', 'bar');
        $this->is($builder->resolveValue(array('%foo%' => '%foo%')), array('bar' => 'bar'), '->resolveValue() replaces placeholders in keys and values of arrays');

        $this->is($builder->resolveValue(array('%foo%' => array('%foo%' => array('%foo%' => '%foo%')))), array('bar' => array('bar' => array('bar' => 'bar'))), '->resolveValue() replaces placeholders in nested arrays');

        $this->is($builder->resolveValue('I\'m a %%foo%%'), 'I\'m a %foo%', '->resolveValue() supports % escaping by doubling it');
        $this->is($builder->resolveValue('I\'m a %foo% %%foo %foo%'), 'I\'m a bar %foo bar', '->resolveValue() supports % escaping by doubling it');

        try {
            $builder->resolveValue('%foobar%');
            $this->fail('->resolveValue() throws a InvalidArgumentException if a placeholder references a non-existant parameter');
        } catch (InvalidArgumentException $e) {
            $this->pass('->resolveValue() throws a InvalidArgumentException if a placeholder references a non-existant parameter');
        }

        try {
            $builder->resolveValue('foo %foobar% bar');
            $this->fail('->resolveValue() throws a InvalidArgumentException if a placeholder references a non-existant parameter');
        } catch (InvalidArgumentException $e) {
            $this->pass('->resolveValue() throws a InvalidArgumentException if a placeholder references a non-existant parameter');
        }

        // ->resolveServices()
        $this->diag('->resolveServices()');
        $builder = new sfServiceContainerBuilder();
        $builder->register('foo', 'FooClass');
        $this->is($builder->resolveServices(new sfServiceReference('foo')), $builder->getService('foo'), '->resolveServices() resolves service references to service instances');
        $this->is($builder->resolveServices(array('foo' => array('foo', new sfServiceReference('foo')))), array('foo' => array('foo', $builder->getService('foo'))), '->resolveServices() resolves service references to service instances in nested arrays');
    }
}
