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
require_once __DIR__.'/../fixtures/ValidatorChoiceTestIsEmpty.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfValidatorChoiceTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function choice_callable()
    {
        return array(1, 2, 3);
    }

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        try {
            new sfValidatorChoice();
            $this->fail('__construct() throws an RuntimeException if you don\'t pass an expected option');
        } catch (RuntimeException $e) {
            $this->pass('__construct() throws an RuntimeException if you don\'t pass an expected option');
        }

        $v = new ValidatorChoiceTestIsEmpty(array('choices' => array('foo', 'bar')));

        // ->isEmpty()
        $this->diag('->isEmpty()');
        $this->is($v->run(array('', '')), true, '->isEmpty() return true if array has only empty value(s)');

        $v = new sfValidatorChoice(array('choices' => array('foo', 'bar')));

        // ->clean()
        $this->diag('->clean()');
        $this->is($v->clean('foo'), 'foo', '->clean() checks that the value is an expected value');
        $this->is($v->clean('bar'), 'bar', '->clean() checks that the value is an expected value');

        try {
            $v->clean('foobar');
            $this->fail('->clean() throws an sfValidatorError if the value is not an expected value');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the value is not an expected value');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        // ->asString()
        $this->diag('->asString()');
        $this->is($v->asString(), 'Choice({ choices: [foo, bar] })', '->asString() returns a string representation of the validator');

        // choices as a callable
        $this->diag('choices as a callable');
        $v = new sfValidatorChoice(array('choices' => new sfCallable(array($this, 'choice_callable'))));
        $this->is($v->clean('2'), '2', '__construct() can take a sfCallable object as a choices option');

        // see bug #4212
        $v = new sfValidatorChoice(array('choices' => array(0, 1, 2)));
        try {
            $v->clean('xxx');
            $this->fail('->clean() throws an sfValidatorError if the value is not strictly an expected value');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the value is not strictly an expected value');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        // min/max options
        $v = new sfValidatorChoice(array('multiple' => true, 'choices' => array(0, 1, 2, 3, 4, 5), 'min' => 2, 'max' => 3));
        try {
            $v->clean(array(0));
            $this->fail('->clean() throws an sfValidatorError if the minimum number of values are not selected');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the minimum number of values are not selected');
            $this->is($e->getCode(), 'min', '->clean() throws a sfValidatorError');
        }

        try {
            $v->clean(array(0, 1, 2, 3));
            $this->fail('->clean() throws an sfValidatorError if more than the maximum number of values are selected');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if more than the maximum number of values are selected');
            $this->is($e->getCode(), 'max', '->clean() throws a sfValidatorError');
        }
    }
}
