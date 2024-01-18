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
class sfValidatorOrTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v1 = new sfValidatorString(array('max_length' => 3));
        $v2 = new sfValidatorString(array('min_length' => 3));

        $v = new sfValidatorOr(array($v1, $v2));

        // __construct()
        $this->diag('__construct()');
        $v = new sfValidatorOr();
        $this->is($v->getValidators(), array(), '->__construct() can take no argument');
        $v = new sfValidatorOr($v1);
        $this->is($v->getValidators(), array($v1), '->__construct() can take a validator as its first argument');
        $v = new sfValidatorOr(array($v1, $v2));
        $this->is($v->getValidators(), array($v1, $v2), '->__construct() can take an array of validators as its first argument');
        try {
            $v = new sfValidatorOr('string');
            $this->fail('_construct() throws an exception when passing a non supported first argument');
        } catch (InvalidArgumentException $e) {
            $this->pass('_construct() throws an exception when passing a non supported first argument');
        }

        // ->addValidator()
        $this->diag('->addValidator()');
        $v = new sfValidatorOr();
        $v->addValidator($v1);
        $v->addValidator($v2);
        $this->is($v->getValidators(), array($v1, $v2), '->addValidator() adds a validator');

        // ->clean()
        $this->diag('->clean()');
        $this->is($v->clean('foo'), 'foo', '->clean() returns the string unmodified');

        try {
            $v->setOption('required', true);
            $v->clean(null);
            $this->fail('->clean() throws an sfValidatorError exception if the input value is required');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError exception if the input value is required');
            $this->is($e->getCode(), 'required', '->clean() throws a sfValidatorError');
        }

        $v1->setOption('max_length', 1);
        $v2->setOption('min_length', 5);
        try {
            $v->clean('foo');
            $this->fail('->clean() throws an sfValidatorError exception if all the validators fails');
            $this->skip('', 3);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError exception if all the validators fails');
            $this->is(count($e), 2, '->clean() throws an exception with all error messages');
            $this->is($e[0]->getCode(), 'max_length', '->clean() throws a sfValidatorSchemaError');
            $this->ok($e instanceof sfValidatorErrorSchema, '->clean() throws a sfValidatorSchemaError');
        }

        try {
            $v->setMessage('invalid', 'Invalid.');
            $v->clean('foo');
            $this->fail('->clean() throws an sfValidatorError exception if one of the validators fails');
            $this->skip('', 2);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError exception if one of the validators fails');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError if invalid message is not empty');
            $this->ok(!$e instanceof sfValidatorErrorSchema, '->clean() throws a sfValidatorError if invalid message is not empty');
        }

        $v1->setOption('max_length', 3);
        $v2->setOption('min_length', 1);
        $this->is($v->clean('foo'), 'foo', '->clean() returns the string unmodified');

        // ->asString()
        $this->diag('->asString()');
        $v1 = new sfValidatorString(array('max_length' => 3));
        $v2 = new sfValidatorString(array('min_length' => 3));
        $v = new sfValidatorOr(array($v1, $v2));
        $this->is($v->asString(), "(\n  String({ max_length: 3 })\n  or\n  String({ min_length: 3 })\n)", '->asString() returns a string representation of the validator');

        $v = new sfValidatorOr(array($v1, $v2), array(), array('required' => 'This is required.'));
        $this->is($v->asString(), "(\n  String({ max_length: 3 })\n  or({}, { required: 'This is required.' })\n  String({ min_length: 3 })\n)", '->asString() returns a string representation of the validator');
    }
}
