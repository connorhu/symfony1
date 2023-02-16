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
require_once __DIR__.'/../fixtures/ProjectServiceContainer.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfServiceContainerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        $sc = new sfServiceContainer();
        $this->is(spl_object_hash($sc->getService('service_container')), spl_object_hash($sc), '__construct() automatically registers itself as a service');

        $sc = new sfServiceContainer(array('foo' => 'bar'));
        $this->is($sc->getParameters(), array('foo' => 'bar'), '__construct() takes an array of parameters as its first argument');

        // ->setParameters() ->getParameters()
        $this->diag('->setParameters() ->getParameters()');

        $sc = new sfServiceContainer();
        $this->is($sc->getParameters(), array(), '->getParameters() returns an empty array if no parameter has been defined');

        $sc->setParameters(array('foo' => 'bar'));
        $this->is($sc->getParameters(), array('foo' => 'bar'), '->setParameters() sets the parameters');

        $sc->setParameters(array('bar' => 'foo'));
        $this->is($sc->getParameters(), array('bar' => 'foo'), '->setParameters() overrides the previous defined parameters');

        $sc->setParameters(array('Bar' => 'foo'));
        $this->is($sc->getParameters(), array('bar' => 'foo'), '->setParameters() converts the key to lowercase');

        // ->setParameter() ->getParameter()
        $this->diag('->setParameter() ->getParameter() ');

        $sc = new sfServiceContainer(array('foo' => 'bar'));
        $sc->setParameter('bar', 'foo');
        $this->is($sc->getParameter('bar'), 'foo', '->setParameter() sets the value of a new parameter');
        $this->is($sc->getParameter('bar'), 'foo', '->getParameter() gets the value of a parameter');

        $sc->setParameter('foo', 'baz');
        $this->is($sc->getParameter('foo'), 'baz', '->setParameter() overrides previously set parameter');

        $sc->setParameter('Foo', 'baz1');
        $this->is($sc->getParameter('foo'), 'baz1', '->setParameter() converts the key to lowercase');
        $this->is($sc->getParameter('FOO'), 'baz1', '->getParameter() converts the key to lowercase');

        try {
            $sc->getParameter('baba');
            $this->fail('->getParameter() thrown an InvalidArgumentException if the key does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->getParameter() thrown an InvalidArgumentException if the key does not exist');
        }

        // ->hasParameter()
        $this->diag('->hasParameter()');
        $sc = new sfServiceContainer(array('foo' => 'bar'));
        $this->ok($sc->hasParameter('foo'), '->hasParameter() returns true if a parameter is defined');
        $this->ok($sc->hasParameter('Foo'), '->hasParameter() converts the key to lowercase');
        $this->ok(!$sc->hasParameter('bar'), '->hasParameter() returns false if a parameter is not defined');

        // ->addParameters()
        $this->diag('->addParameters()');
        $sc = new sfServiceContainer(array('foo' => 'bar'));
        $sc->addParameters(array('bar' => 'foo'));
        $this->is($sc->getParameters(), array('foo' => 'bar', 'bar' => 'foo'), '->addParameters() adds parameters to the existing ones');
        $sc->addParameters(array('Bar' => 'fooz'));
        $this->is($sc->getParameters(), array('foo' => 'bar', 'bar' => 'fooz'), '->addParameters() converts keys to lowercase');

        // ->setService() ->hasService() ->getService()
        $this->diag('->setService() ->hasService() ->getService()');
        $sc = new sfServiceContainer();
        $sc->setService('foo', $obj = new stdClass());
        $this->is(spl_object_hash($sc->getService('foo')), spl_object_hash($obj), '->setService() registers a service under a key name');

        $this->ok($sc->hasService('foo'), '->hasService() returns true if the service is defined');
        $this->ok(!$sc->hasService('bar'), '->hasService() returns false if the service is not defined');

        // ->getServiceIds()
        $this->diag('->getServiceIds()');
        $sc = new sfServiceContainer();
        $sc->setService('foo', $obj = new stdClass());
        $sc->setService('bar', $obj = new stdClass());
        $this->is($sc->getServiceIds(), array('service_container', 'foo', 'bar'), '->getServiceIds() returns all defined service ids');

        $sc = new ProjectServiceContainer();
        $this->is(spl_object_hash($sc->getService('bar')), spl_object_hash($sc->__bar), '->getService() looks for a getXXXService() method');
        $this->ok($sc->hasService('bar'), '->hasService() returns true if the service has been defined as a getXXXService() method');

        $sc->setService('bar', $bar = new stdClass());
        $this->is(spl_object_hash($sc->getService('bar')), spl_object_hash($bar), '->getService() prefers to return a service defined with setService() than one defined with a getXXXService() method');

        try {
            $sc->getService('baba');
            $this->fail('->getService() thrown an InvalidArgumentException if the service does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->getService() thrown an InvalidArgumentException if the service does not exist');
        }

        $this->is(spl_object_hash($sc->getService('foo_bar')), spl_object_hash($sc->__foo_bar), '->getService() camelizes the service id when looking for a method');
        $this->is(spl_object_hash($sc->getService('foo.baz')), spl_object_hash($sc->__foo_baz), '->getService() camelizes the service id when looking for a method');
    }
}
