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
require_once __DIR__.'/../fixtures/ProjectLoader2.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfServiceContainerLoaderArrayTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $loader = new ProjectLoader2(null);

        // ->validate()
        try {
            $loader->validate(sfYaml::load(__DIR__.'/../fixtures/service/yaml/nonvalid1.yml'));
            $this->fail('->validate() throws an InvalidArgumentException if the loaded definition is not an array');
        } catch (InvalidArgumentException $e) {
            $this->pass('->validate() throws an InvalidArgumentException if the loaded definition is not an array');
        }

        try {
            $loader->validate(sfYaml::load(__DIR__.'/../fixtures/service/yaml/nonvalid2.yml'));
            $this->fail('->validate() throws an InvalidArgumentException if the loaded definition is not a valid array');
        } catch (InvalidArgumentException $e) {
            $this->pass('->validate() throws an InvalidArgumentException if the loaded definition is not a valid array');
        }

        // ->load() # parameters
        $this->diag('->load() # parameters');

        list($services, $parameters) = $loader->doLoad(array());
        $this->is($parameters, array(), '->load() return emty parameters array for an empty array definition');

        list($services, $parameters) = $loader->doLoad(sfYaml::load(__DIR__.'/../fixtures/service/yaml/services2.yml'));
        $this->is(array_keys($parameters), array('foo', 'values', 'bar', 'foo_bar'), '->load() converts array keys to lowercase');
        $this->is($parameters['foo'], 'bar', '->load() converts array keys to lowercase');
        $this->is($parameters['values'], array(true, false, 0, 1000.3), '->load() converts array keys to lowercase');
        $this->is($parameters['bar'], 'foo', '->load() converts array keys to lowercase');
        $this->is($parameters['foo_bar'] instanceof sfServiceReference, true, '->load() converts array keys to lowercase');
        $this->is($parameters['foo_bar']->__toString(), 'foo_bar', '->load() converts array keys to lowercase');

        // ->load() # services
        list($services, $parameters) = $loader->doLoad(sfYaml::load(__DIR__.'/../fixtures/service/yaml/services2.yml'));
        $this->is($services, array(), '->load() return emty services array for an empty array definition');

        $this->diag('->load() # services');
        list($services, $parameters) = $loader->doLoad(sfYaml::load(__DIR__.'/../fixtures/service/yaml/services3.yml'));
        $this->ok(isset($services['foo']), '->load() parses service elements');
        $this->is(get_class($services['foo']), 'sfServiceDefinition', '->load() converts service element to sfServiceDefinition instances');
        $this->is($services['foo']->getClass(), 'FooClass', '->load() parses the class attribute');
        $this->ok($services['shared']->isShared(), '->load() parses the shared attribute');
        $this->ok(!$services['non_shared']->isShared(), '->load() parses the shared attribute');
        $this->is($services['constructor']->getConstructor(), 'getInstance', '->load() parses the constructor attribute');
        $this->is($services['file']->getFile(), '%path%/foo.php', '->load() parses the file tag');
        $this->is($services['arguments']->getArguments()[0], 'foo', '->load() parses the argument tags');
        $this->is($services['arguments']->getArguments()[1]->__toString(), 'foo', '->load() parses the argument tags');
        $this->is($services['arguments']->getArguments()[1] instanceof sfServiceReference, true, '->load() parses the argument tags');
        $this->is($services['arguments']->getArguments()[2], array(true, false), '->load() parses the argument tags');
        $this->is($services['configurator1']->getConfigurator(), 'sc_configure', '->load() parses the configurator tag');
        $this->is($services['configurator2']->getConfigurator()[0] instanceof sfServiceReference, true, '->load() parses the configurator tag');
        $this->is($services['configurator2']->getConfigurator()[0]->__toString(), 'baz', '->load() parses the configurator tag');
        $this->is($services['configurator2']->getConfigurator()[1], 'configure', '->load() parses the configurator tag');
        $this->is($services['configurator3']->getConfigurator(), array('BazClass', 'configureStatic'), '->load() parses the configurator tag');
        $this->is($services['method_call1']->getMethodCalls(), array(array('setBar', array())), '->load() parses the method_call tag');
        $this->is($services['method_call2']->getMethodCalls()[0][0], 'setBar', '->load() parses the method_call tag');
        $this->is($services['method_call2']->getMethodCalls()[0][1][0], 'foo', '->load() parses the method_call tag');
        $this->is($services['method_call2']->getMethodCalls()[0][1][1] instanceof sfServiceReference, true, '->load() parses the method_call tag');
        $this->is($services['method_call2']->getMethodCalls()[0][1][1]->__toString(), 'foo', '->load() parses the method_call tag');
        $this->is($services['method_call2']->getMethodCalls()[0][1][2], array(true, false), '->load() parses the method_call tag');
        $this->ok(isset($services['alias_for_foo']), '->load() parses aliases');
        $this->is($services['alias_for_foo'], 'foo', '->load() parses aliases');
    }
}
