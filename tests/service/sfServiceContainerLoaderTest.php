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
require_once __DIR__.'/../fixtures/ProjectLoader.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfServiceContainerLoaderTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        $loader = new ProjectLoader($container = new sfServiceContainerBuilder());
        $this->is($loader->container, $container, '__construct() takes a container builder instance as its first argument');

        // ->setServiceContainer()
        $this->diag('->setServiceContainer()');
        $loader = new ProjectLoader();
        $loader->setServiceContainer($container = new sfServiceContainerBuilder());
        $this->is($loader->container, $container, '->setServiceContainer() sets the container builder attached to this loader');

        // ->load()
        $this->diag('->load()');
        $loader = new ProjectLoader();
        try {
            $loader->load('foo');
            $this->fail('->load() throws a LogicException if no container is attached to the loader');
        } catch (LogicException $e) {
            $this->pass('->load() throws a LogicException if no container is attached to the loader');
        }

        $loader->setServiceContainer($container = new sfServiceContainerBuilder(array('bar' => 'foo')));
        $loader->load(array(array(), array('foo' => 'bar')));
        $this->is($container->getParameters(), array('bar' => 'foo', 'foo' => 'bar'), '->load() merges current parameters with the loaded ones');

        $loader->setServiceContainer($container = new sfServiceContainerBuilder(array('bar' => 'foo', 'foo' => 'baz')));
        $loader->load(array(array(), array('foo' => 'bar')));
        $this->is($container->getParameters(), array('bar' => 'foo', 'foo' => 'baz'), '->load() does not change the already defined parameters');

        $loader->setServiceContainer($container = new sfServiceContainerBuilder(array('bar' => 'foo')));
        $loader->load(array(array(), array('foo' => '%bar%')));
        $this->is($container->getParameters(), array('bar' => 'foo', 'foo' => 'foo'), '->load() evaluates the values of the parameters towards already defined ones');

        $loader->setServiceContainer($container = new sfServiceContainerBuilder(array('bar' => 'foo')));
        $loader->load(array(array(), array('foo' => '%bar%', 'baz' => '%foo%')));
        $this->is($container->getParameters(), array('bar' => 'foo', 'foo' => 'foo', 'baz' => 'foo'), '->load() evaluates the values of the parameters towards already defined ones');

        $loader->setServiceContainer($container = new sfServiceContainerBuilder());
        $container->register('foo', 'FooClass');
        $container->register('bar', 'BarClass');
        $loader->load(array(array('baz' => new sfServiceDefinition('BazClass'), 'alias_for_foo' => 'foo'), array()));
        $this->is(array_keys($container->getServiceDefinitions()), array('foo', 'bar', 'baz'), '->load() merges definitions already defined ones');
        $this->is($container->getAliases(), array('alias_for_foo' => 'foo'), '->load() registers defined aliases');

        $loader->setServiceContainer($container = new sfServiceContainerBuilder());
        $container->register('foo', 'FooClass');
        $loader->load(array(array('foo' => new sfServiceDefinition('BazClass')), array()));
        $this->is($container->getServiceDefinition('foo')->getClass(), 'BazClass', '->load() overrides already defined services');

        $loader->setServiceContainer($container = new sfServiceContainerBuilder());
        $loader->load(array(array(), array('foo' => 'bar')), array(array(), array('bar' => 'foo')));
        $this->is($container->getParameters(), array('foo' => 'bar', 'bar' => 'foo'), '->load() accepts several resources as argument');
    }
}
