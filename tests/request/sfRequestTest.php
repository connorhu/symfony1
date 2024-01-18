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
require_once __DIR__.'/../fixtures/myRequest3.php';
require_once __DIR__.'/../fixtures/fakeRequest.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfRequestTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dispatcher = new sfEventDispatcher();

        // ->initialize()
        $this->diag('->initialize()');
        $request = new myRequest3($dispatcher);
        $this->is($dispatcher, $request->getEventDispatcher(), '->initialize() takes a sfEventDispatcher object as its first argument');
        $request->initialize($dispatcher, array('foo' => 'bar'));
        $this->is($request->getParameter('foo'), 'bar', '->initialize() takes an array of parameters as its second argument');

        $options = $request->getOptions();
        $this->is($options['logging'], false, '->getOptions() returns options for request instance');

        // ->getMethod() ->setMethod()
        $this->diag('->getMethod() ->setMethod()');
        $request->setMethod(sfRequest::GET);
        $this->is($request->getMethod(), sfRequest::GET, '->getMethod() returns the current request method');

        try {
            $request->setMethod('foo');
            $this->fail('->setMethod() throws a sfException if the method is not valid');
        } catch (sfException $e) {
            $this->pass('->setMethod() throws a sfException if the method is not valid');
        }

// ->extractParameters()
        $this->diag('->extractParameters()');
        $request->initialize($dispatcher, array('foo' => 'foo', 'bar' => 'bar'));
        $this->is($request->extractParameters(array()), array(), '->extractParameters() returns parameters');
        $this->is($request->extractParameters(array('foo')), array('foo' => 'foo'), '->extractParameters() returns parameters for keys in its first parameter');
        $this->is($request->extractParameters(array('bar')), array('bar' => 'bar'), '->extractParameters() returns parameters for keys in its first parameter');

        // array access for request parameters
        $this->diag('Array access for request parameters');
        $this->is(isset($request['foo']), true, '->offsetExists() returns true if request parameter exists');
        $this->is(isset($request['foo2']), false, '->offsetExists() returns false if request parameter does not exist');
        $this->is($request['foo3'], false, '->offsetGet() returns false if parameter does not exist');
        $this->is($request['foo'], 'foo', '->offsetGet() returns parameter by name');

        $request['foo2'] = 'foo2';
        $this->is($request['foo2'], 'foo2', '->offsetSet() sets parameter by name');

        unset($request['foo2']);
        $this->is(isset($request['foo2']), false, '->offsetUnset() unsets parameter by name');

        // ->getOption()
        $this->diag('->getOption()');
        $request = new myRequest3($dispatcher, array(), array(), array('val_1' => 'value', 'val_2' => false));
        $this->is($request->getOption('val_1'), 'value', '->getOption() returns the option value if exists');
        $this->is($request->getOption('val_2'), false, '->getOption() returns the option value if exists');
        $this->is($request->getOption('none'), null, '->getOption() returns the option value if not exists');

        // ->getOption()
        $this->diag('->__clone()');
        $request = new myRequest3($dispatcher);
        $requestClone = clone $request;
        $this->ok($request->getParameterHolder() !== $requestClone->getParameterHolder(), '->__clone() clone parameterHolder');
        $this->ok($request->getAttributeHolder() !== $requestClone->getAttributeHolder(), '->__clone() clone attributeHolder');
    }
}
