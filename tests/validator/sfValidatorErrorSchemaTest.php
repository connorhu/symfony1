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
require_once __DIR__.'/../fixtures/NotSerializable.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfValidatorErrorSchemaTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v1 = new sfValidatorString();
        $v2 = new sfValidatorString();

        $e1 = new sfValidatorError($v1, 'max_length', array('value' => 'foo', 'max_length' => 1));
        $e2 = new sfValidatorError($v2, 'min_length', array('value' => 'bar', 'min_length' => 5));

        $e = new sfValidatorErrorSchema($v1);

        // __construct()
        $this->diag('__construct()');
        $this->is($e->getValidator(), $v1, '__construct() takes a sfValidator as its first argument');
        $e = new sfValidatorErrorSchema($v1, array('e1' => $e1, 'e2' => $e2));
        $this->is($e->getErrors(), array('e1' => $e1, 'e2' => $e2), '__construct() can take an array of sfValidatorError as its second argument (depreciated)');

        // ->addError() ->getErrors()
        $this->diag('->addError() ->getErrors()');
        $e = new sfValidatorErrorSchema($v1);
        $e->addError($e1);
        $e->addError($e2, 'e2');
        $e->addError($e1, '2');
        $this->is($e->getErrors(), array($e1, 'e2' => $e2, '2' => $e1), '->addError() adds an error to the error schema');

        $this->diag('embedded errors');
        $es1 = new sfValidatorErrorSchema(new sfValidatorString());
        $es1->addError($e1);
        $es1->addError($e1, 'e1');
        $es1->addError($e2, 'e2');

        $es = new sfValidatorErrorSchema(new sfValidatorString());
        $es->addError($e1);
        $es->addError($e1, 'e1');
        $es->addError($es1, 'e2');
        $es->addError($e2, 'e1');
        $this->is($es->getCode(), 'max_length e1 [max_length min_length] e2 [max_length e1 [max_length] e2 [min_length]]', '->addError() adds an error to the error schema');

        $es->addError($e2);
        $this->is($es->getCode(), 'max_length min_length e1 [max_length min_length] e2 [max_length e1 [max_length] e2 [min_length]]', '->addError() adds an error to the error schema');

        $es2 = new sfValidatorErrorSchema(new sfValidatorString());
        $es2->addError($e1);
        $es2->addError($e1, 'e1');
        $es2->addError($e2, 'e2');

        $es->addError($es2, 'e3');
        $this->is($es->getCode(), 'max_length min_length e1 [max_length min_length] e2 [max_length e1 [max_length] e2 [min_length]] e3 [max_length e1 [max_length] e2 [min_length]]', '->addError() adds an error to the error schema');

        $es3 = new sfValidatorErrorSchema(new sfValidatorString());
        $es3->addError($e1);
        $es3->addError($e1, 'e1');
        $es3->addError($e2, 'e2');

        $es->addError($es3);
        $this->is($es->getCode(), 'max_length min_length max_length e1 [max_length min_length max_length] e2 [max_length min_length e1 [max_length] e2 [min_length]] e3 [max_length e1 [max_length] e2 [min_length]]', '->addError() adds an error to the error schema');

        $es1 = new sfValidatorErrorSchema($v1);
        $es1->addError($e1);
        $es1->addError($e1, 'e1');
        $es1->addError($e2, 'e2');
        $es = new sfValidatorErrorSchema($v1);
        $es->addError($e1);
        $es->addError($e1, 'e1');
        $es->addError($es1, 'e2');

        $es1 = new sfValidatorErrorSchema($v1);
        $es1->addError($e1);
        $es1->addError($e1, 'e1');
        $es1->addError($e2, 'e2');
        $es2 = new sfValidatorErrorSchema($v1);
        $es2->addError($e1);
        $es2->addError($e1, 'e1');
        $es2->addError($es1, 'e2');

        $es->addError($es2, 'e2');
        $this->is($es->getCode(), 'max_length e1 [max_length] e2 [max_length max_length e1 [max_length max_length] e2 [min_length max_length e1 [max_length] e2 [min_length]]]', '->addError() adds an error to the error schema');

        // ->addErrors()
        $this->diag('->addErrors()');
        $es1 = new sfValidatorErrorSchema($v1);
        $es1->addError($e1);
        $es1->addError($e1, 0);
        $es1->addError($e2, '1');
        $es = new sfValidatorErrorSchema($v1);
        $es->addErrors($es1);
        $this->is($es->getGlobalErrors(), array($e1), '->addErrors() adds an array of errors to the current error');
        $this->is($es->getNamedErrors(), array(0 => $e1, '1' => $e2), '->addErrors() merges a sfValidatorErrorSchema to the current error');

        // ->getGlobalErrors()
        $this->diag('->getGlobalErrors()');
        $e = new sfValidatorErrorSchema($v1);
        $e->addError($e1);
        $e->addError($e2, 'e2');
        $e->addError($e1, '2');
        $this->is($e->getGlobalErrors(), array($e1), '->getGlobalErrors() returns all globals/non named errors');

        // ->getNamedErrors()
        $this->diag('->getNamedErrors()');
        $this->is($e->getNamedErrors(), array('e2' => $e2, '2' => $e1), '->getNamedErrors() returns all named errors');

        // ->getValue()
        $this->diag('->getValue()');
        $this->is($e->getValue(), null, '->getValue() always returns null');

        // ->getArguments()
        $this->diag('->getArguments()');
        $this->is($e->getArguments(), array(), '->getArguments() always returns an empty array');
        $this->is($e->getArguments(true), array(), '->getArguments() always returns an empty array');

        // ->getMessageFormat()
        $this->diag('->getMessageFormat()');
        $this->is($e->getMessageFormat(), '', '->getMessageFormat() always returns an empty string');

        // ->getMessage()
        $this->diag('->getMessage()');
        $this->is($e->getMessage(), '"foo" is too long (1 characters max). e2 ["bar" is too short (5 characters min).] 2 ["foo" is too long (1 characters max).]', '->getMessage() returns the error message string');

        // ->getCode()
        $this->diag('->getCode()');
        $this->is($e->getCode(), 'max_length e2 [min_length] 2 [max_length]', '->getCode() returns the error code');

        // implements Countable
        $this->diag('implements Countable');
        $e = new sfValidatorErrorSchema($v1);
        $e->addError($e1, 'e1');
        $e->addError($e2, 'e2');
        $this->is(count($e), 2, '"sfValidatorError" implements Countable');

        // implements Iterator
        $this->diag('implements Iterator');
        $e = new sfValidatorErrorSchema($v1);
        $e->addError($e1, 'e1');
        $e->addError($e2);
        $e->addError($e2, '2');
        $errors = array();
        foreach ($e as $name => $error) {
            $errors[$name] = $error;
        }
        $this->is($errors, array('e1' => $e1, 0 => $e2, '2' => $e2), '"sfValidatorErrorSchema" implements the Iterator interface');

        // implements ArrayAccess
        $this->diag('implements ArrayAccess');
        $e = new sfValidatorErrorSchema($v1);
        $e->addError($e1, 'e1');
        $e->addError($e2);
        $e->addError($e2, '2');
        $this->is($e['e1'], $e1, '"sfValidatorErrorSchema" implements the ArrayAccess interface');
        $this->is($e[0], $e2, '"sfValidatorErrorSchema" implements the ArrayAccess interface');
        $this->is($e['2'], $e2, '"sfValidatorErrorSchema" implements the ArrayAccess interface');
        $this->is(isset($e['e1']), true, '"sfValidatorErrorSchema" implements the ArrayAccess interface');
        $this->is(isset($e['e2']), false, '"sfValidatorErrorSchema" implements the ArrayAccess interface');
        try {
            $e['e1'] = $e2;
            $this->fail('"sfValidatorErrorSchema" implements the ArrayAccess interface');
        } catch (LogicException $e) {
            $this->pass('"sfValidatorErrorSchema" implements the ArrayAccess interface');
        }

        // implements Serializable
        $this->diag('implements Serializable');

        function will_crash($a)
        {
            return serialize(new sfValidatorErrorSchema(new sfValidatorString()));
        }

        $a = new NotSerializable();

        try {
            $serialized = will_crash($a);
            $this->pass('"sfValidatorErrorSchema" implements Serializable');
        } catch (Exception $e) {
            $this->fail('"sfValidatorErrorSchema" implements Serializable');
        }

        $e = new sfValidatorErrorSchema($v1);
        $e1 = unserialize($serialized);
        $this->is($e1->getMessage(), $e->getMessage(), '"sfValidatorErrorSchema" implements Serializable');
        $this->is($e1->getCode(), $e->getCode(), '"sfValidatorErrorSchema" implements Serializable');
        $this->is(get_class($e1->getValidator()), get_class($e->getValidator()), '"sfValidatorErrorSchema" implements Serializable');
        $this->is($e1->getArguments(), $e->getArguments(), '"sfValidatorErrorSchema" implements Serializable');
        $this->is($e1->getNamedErrors(), $e->getNamedErrors(), '"sfValidatorErrorSchema" implements Serializable');
        $this->is($e1->getGlobalErrors(), $e->getGlobalErrors(), '"sfValidatorErrorSchema" implements Serializable');
    }
}
