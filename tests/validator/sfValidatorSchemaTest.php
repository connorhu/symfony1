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
require_once __DIR__.'/../fixtures/PreValidator.php';
require_once __DIR__.'/../fixtures/PostValidator.php';
require_once __DIR__.'/../fixtures/Post1Validator.php';
require_once __DIR__.'/../fixtures/BytesValidatorSchema.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfValidatorSchemaTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v1 = new sfValidatorString(array('max_length' => 3));
        $v2 = new sfValidatorString(array('min_length' => 3));

        // __construct()
        $this->diag('__construct()');
        $v = new sfValidatorSchema();
        $this->is($v->getFields(), array(), '->__construct() can take no argument');
        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));

        $fields = $v->getFields();
        $this->assertSame(array('s1', 's2'), array_keys($fields), '->__construct() can take an array of named sfValidator objects');
        $this->assertSame(3, $fields['s1']->getOption('max_length'), '->__construct() can take an array of named sfValidator objects');
        $this->assertSame(3, $fields['s2']->getOption('min_length'), '->__construct() can take an array of named sfValidator objects');

        try {
            $v = new sfValidatorSchema('string');
            $this->fail('__construct() throws an InvalidArgumentException when passing a non supported first argument');
        } catch (InvalidArgumentException $e) {
            $this->pass('__construct() throws an InvalidArgumentException when passing a non supported first argument');
        }

        // implements ArrayAccess
        $this->diag('implements ArrayAccess');
        $v = new sfValidatorSchema();
        $v['s1'] = $v1;
        $v['s2'] = $v2;

        $fields = $v->getFields();
        $this->assertSame(array('s1', 's2'), array_keys($fields), 'sfValidatorSchema implements the ArrayAccess interface for the fields');
        $this->assertSame(3, $fields['s1']->getOption('max_length'), 'sfValidatorSchema implements the ArrayAccess interface for the fields');
        $this->assertSame(3, $fields['s2']->getOption('min_length'), 'sfValidatorSchema implements the ArrayAccess interface for the fields');

        try {
            $v['v1'] = 'string';
            $this->fail('sfValidatorSchema implements the ArrayAccess interface for the fields');
        } catch (InvalidArgumentException $e) {
            $this->pass('sfValidatorSchema implements the ArrayAccess interface for the fields');
        }

        $v = new sfValidatorSchema(array('s1' => $v1));
        $this->is(isset($v['s1']), true, 'sfValidatorSchema implements the ArrayAccess interface for the fields');
        $this->is(isset($v['s2']), false, 'sfValidatorSchema implements the ArrayAccess interface for the fields');

        $v = new sfValidatorSchema(array('s1' => $v1));
        $this->ok($v['s1'] == $v1, 'sfValidatorSchema implements the ArrayAccess interface for the fields');
        $this->is($v['s2'], null, 'sfValidatorSchema implements the ArrayAccess interface for the fields');

        $v = new sfValidatorSchema(array('v1' => $v1));
        unset($v['s1']);
        $this->is($v['s1'], null, 'sfValidatorSchema implements the ArrayAccess interface for the fields');

        // ->configure()
        $this->diag('->configure()');
        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));
        $this->is($v->getOption('allow_extra_fields'), false, '->configure() sets "allow_extra_fields" option to false by default');
        $this->is($v->getOption('filter_extra_fields'), true, '->configure() sets "filter_extra_fields" option to true by default');
        $this->is($v->getMessage('extra_fields'), 'Unexpected extra form field named "%field%".', '->configure() has a default error message for the "extra_fields" error');

        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2), array('allow_extra_fields' => true, 'filter_extra_fields' => false), array('extra_fields' => 'Extra fields'));
        $this->is($v->getOption('allow_extra_fields'), true, '->__construct() can override the default value for the "allow_extra_fields" option');
        $this->is($v->getOption('filter_extra_fields'), false, '->__construct() can override the default value for the "filter_extra_fields" option');

        $this->is($v->getMessage('extra_fields'), 'Extra fields', '->__construct() can override the default message for the "extra_fields" error message');

        // ->clean()
        $this->diag('->clean()');

        $v = new sfValidatorSchema();
        $this->is($v->clean(null), array(), '->clean() converts null to empty array before validation');

        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));

        try {
            $v->clean('foo');
            $this->fail('->clean() throws an InvalidArgumentException exception if the first argument is not an array of value');
        } catch (InvalidArgumentException $e) {
            $this->pass('->clean() throws an InvalidArgumentException exception if the first argument is not an array of value');
        }

        $this->is($v->clean(array('s1' => 'foo', 's2' => 'bar')), array('s1' => 'foo', 's2' => 'bar'), '->clean() returns the string unmodified');

        try {
            $v->clean(array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'));
            $this->fail('->clean() throws an sfValidatorErrorSchema exception if a you give a non existant field');
            $this->skip('', 2);
        } catch (sfValidatorErrorSchema $e) {
            $this->pass('->clean() throws an sfValidatorErrorSchema exception if a you give a non existant field');
            $this->is(count($e), 1, '->clean() throws an exception with all error messages');
            $this->is($e[0]->getCode(), 'extra_fields', '->clean() throws an exception with all error messages');
        }

        $this->diag('required fields');
        try {
            $v->clean(array('s1' => 'foo'));
            $this->fail('->clean() throws an sfValidatorErrorSchema exception if a required field is not provided');
            $this->skip('', 2);
        } catch (sfValidatorErrorSchema $e) {
            $this->pass('->clean() throws an sfValidatorErrorSchema exception if a required field is not provided');
            $this->is(count($e), 1, '->clean() throws an exception with all error messages');
            $this->is($e['s2']->getCode(), 'required', '->clean() throws an exception with all error messages');
        }

        // ->getPreValidator() ->setPreValidator()
        $this->diag('->getPreValidator() ->setPreValidator()');
        $v1 = new sfValidatorString(array('max_length' => 3, 'required' => false));
        $v2 = new sfValidatorString(array('min_length' => 3, 'required' => false));
        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));
        $v->setPreValidator($preValidator = new PreValidator());
        $this->ok($v->getPreValidator() == $preValidator, '->getPreValidator() returns the current pre validator');
        try {
            $v->clean(array('s1' => 'foo', 's2' => 'bar'));
            $this->fail('->clean() throws an sfValidatorErrorSchema exception if a pre-validator fails');
            $this->skip('', 2);
        } catch (sfValidatorErrorSchema $e) {
            $this->pass('->clean() throws an sfValidatorErrorSchema exception if a pre-validator fails');
            $this->is(count($e), 1, '->clean() throws an exception with all error messages');
            $this->is($e[0]->getCode(), 's1_or_s2', '->clean() throws an exception with all error messages');
        }

        $s = $v->clean(array('s1' => 'foo'));
        $this->is('FOO', $s['s1'], '->clean() takes values returned by pre-validator');

        // ->getPostValidator() ->setPostValidator()
        $this->diag('->getPostValidator() ->setPostValidator()');
        $v1 = new sfValidatorString(array('max_length' => 3, 'required' => false));
        $v2 = new sfValidatorString(array('min_length' => 3, 'required' => false));
        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));
        $v->setPostValidator($postValidator = new PostValidator());
        $this->ok($v->getPostValidator() == $postValidator, '->getPostValidator() returns the current post validator');
        $this->is($v->clean(array('s1' => 'foo', 's2' => 'bar')), array('s1' => '*foo*', 's2' => '*bar*'), '->clean() executes post validators');

        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));
        $v->setPostValidator(new Post1Validator());
        try {
            $v->clean(array('s1' => 'foo', 's2' => 'foo'));
            $this->fail('->clean() throws an sfValidatorErrorSchema exception if a post-validator fails');
            $this->skip('', 2);
        } catch (sfValidatorErrorSchema $e) {
            $this->pass('->clean() throws an sfValidatorErrorSchema exception if a post-validator fails');
            $this->is(count($e), 1, '->clean() throws an exception with all error messages');
            $this->is($e[0]->getCode(), 's1_not_equal_s2', '->clean() throws an exception with all error messages');
        }

        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));
        $this->is($v->clean(array('s1' => 'foo')), array('s1' => 'foo', 's2' => ''), '->clean() returns the value of empty_value option for fields not present in the input array');

        $this->diag('extra fields');
        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));
        $v->setOption('allow_extra_fields', true);
        $ret = $v->clean(array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'));
        $this->is($ret, array('s1' => 'foo', 's2' => 'bar'), '->clean() filters non existant fields if "allow_extra_fields" is true');

        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2), array('allow_extra_fields' => true));
        $ret = $v->clean(array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'));
        $this->is($ret, array('s1' => 'foo', 's2' => 'bar'), '->clean() filters non existant fields if "allow_extra_fields" is true');

        $v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2), array('allow_extra_fields' => true, 'filter_extra_fields' => false));
        $ret = $v->clean(array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'));
        $this->is($ret, array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'), '->clean() do not filter non existant fields if "filter_extra_fields" is false');

        $v->setOption('filter_extra_fields', false);
        $ret = $v->clean(array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'));
        $this->is($ret, array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'), '->clean() do not filter non existant fields if "filter_extra_fields" is false');

        $this->diag('one validator fails');
        $v['s2']->setOption('max_length', 2);
        try {
            $v->clean(array('s1' => 'foo', 's2' => 'bar'));
            $this->fail('->clean() throws an sfValidatorErrorSchema exception if one of the validators fails');
            $this->skip('', 2);
        } catch (sfValidatorErrorSchema $e) {
            $this->pass('->clean() throws an sfValidatorErrorSchema exception if one of the validators fails');
            $this->is(count($e), 1, '->clean() throws an exception with all error messages');
            $this->is($e['s2']->getCode(), 'max_length', '->clean() throws an exception with all error messages');
        }

        $this->diag('several validators fail');
        $v['s1']->setOption('max_length', 2);
        $v['s2']->setOption('max_length', 2);
        try {
            $v->clean(array('s1' => 'foo', 's2' => 'bar'));
            $this->fail('->clean() throws an sfValidatorErrorSchema exception if one of the validators fails');
            $this->skip('', 3);
        } catch (sfValidatorErrorSchema $e) {
            $this->pass('->clean() throws an sfValidatorErrorSchema exception if one of the validators fails');
            $this->is(count($e), 2, '->clean() throws an exception with all error messages');
            $this->is($e['s2']->getCode(), 'max_length', '->clean() throws an exception with all error messages');
            $this->is($e['s1']->getCode(), 'max_length', '->clean() throws an exception with all error messages');
        }

        $this->diag('postValidator can throw named errors or global errors');
        $comparator = new sfValidatorSchemaCompare('left', sfValidatorSchemaCompare::EQUAL, 'right');
        $userValidator = new sfValidatorSchema(array(
            'test' => new sfValidatorString(array('min_length' => 10)),
            'left' => new sfValidatorString(array('min_length' => 2)),
            'right' => new sfValidatorString(array('min_length' => 2)),
        ));
        $userValidator->setPostValidator($comparator);
        $v = new sfValidatorSchema(array(
            'test' => new sfValidatorString(array('min_length' => 10)),
            'left' => new sfValidatorString(array('min_length' => 2)),
            'right' => new sfValidatorString(array('min_length' => 2)),
            'embedded' => $userValidator,
        ));
        $v->setPostValidator($comparator);

        $this->diag('postValidator throws global errors');
        foreach (array($userValidator->getPostValidator(), $v->getPostValidator(), $v['embedded']->getPostValidator()) as $validator) {
            $validator->setOption('throw_global_error', true);
        }
        try {
            $v->clean(array('test' => 'fabien', 'right' => 'bar', 'embedded' => array('test' => 'fabien', 'left' => 'oof', 'right' => 'rab')));
            $this->skip('', 7);
        } catch (sfValidatorErrorSchema $e) {
            $this->is(count($e->getNamedErrors()), 3, '->clean() throws an exception with all error messages');
            $this->is(count($e->getGlobalErrors()), 1, '->clean() throws an exception with all error messages');
            $this->is(count($e['embedded']->getNamedErrors()), 1, '->clean() throws an exception with all error messages');
            $this->is(count($e['embedded']->getGlobalErrors()), 1, '->clean() throws an exception with all error messages');
            $this->is(isset($e['left']) ? $e['left']->getCode() : '', 'required', '->clean() throws an exception with all error messages');
            $this->is(isset($e['embedded']['left']) ? $e['embedded']['left']->getCode() : '', '', '->clean() throws an exception with all error messages');
            $this->is($e->getCode(), 'invalid test [min_length] embedded [invalid test [min_length]] left [required]', '->clean() throws an exception with all error messages');
        }

        $this->diag('postValidator throws named errors');
        foreach (array($userValidator->getPostValidator(), $v->getPostValidator(), $v['embedded']->getPostValidator()) as $validator) {
            $validator->setOption('throw_global_error', false);
        }
        try {
            $v->clean(array('test' => 'fabien', 'right' => 'bar', 'embedded' => array('test' => 'fabien', 'left' => 'oof', 'right' => 'rab')));
            $this->skip('', 7);
        } catch (sfValidatorErrorSchema $e) {
            $this->is(count($e->getNamedErrors()), 3, '->clean() throws an exception with all error messages');
            $this->is(count($e->getGlobalErrors()), 0, '->clean() throws an exception with all error messages');
            $this->is(count($e['embedded']->getNamedErrors()), 2, '->clean() throws an exception with all error messages');
            $this->is(count($e['embedded']->getGlobalErrors()), 0, '->clean() throws an exception with all error messages');
            $this->is(isset($e['left']) ? $e['left']->getCode() : '', 'required invalid', '->clean() throws an exception with all error messages');
            $this->is(isset($e['embedded']['left']) ? $e['embedded']['left']->getCode() : '', 'invalid', '->clean() throws an exception with all error messages');
            $this->is($e->getCode(), 'test [min_length] embedded [test [min_length] left [invalid]] left [required invalid]', '->clean() throws an exception with all error messages');
        }

        $this->diag('complex postValidator');
        $comparator1 = new sfValidatorSchemaCompare('password', sfValidatorSchemaCompare::EQUAL, 'password_bis');
        $v = new sfValidatorSchema(array(
            'left' => new sfValidatorString(array('min_length' => 2)),
            'right' => new sfValidatorString(array('min_length' => 2)),
            'password' => new sfValidatorString(array('min_length' => 2)),
            'password_bis' => new sfValidatorString(array('min_length' => 2)),
        ));
        $v->setPostValidator(new sfValidatorAnd(array($comparator, $comparator1)));
        try {
            $v->clean(array('left' => 'foo', 'right' => 'bar', 'password' => 'oof', 'password_bis' => 'rab'));
            $this->skip('', 3);
        } catch (sfValidatorErrorSchema $e) {
            $this->is(count($e->getNamedErrors()), 2, '->clean() throws an exception with all error messages');
            $this->is(count($e->getGlobalErrors()), 0, '->clean() throws an exception with all error messages');
            $this->is($e->getCode(), 'left [invalid] password [invalid]', '->clean() throws an exception with all error messages');
        }

        $comparator->setOption('throw_global_error', true);
        try {
            $v->clean(array('left' => 'foo', 'right' => 'bar', 'password' => 'oof', 'password_bis' => 'rab'));
            $this->skip('', 3);
        } catch (sfValidatorErrorSchema $e) {
            $this->is(count($e->getNamedErrors()), 1, '->clean() throws an exception with all error messages');
            $this->is(count($e->getGlobalErrors()), 1, '->clean() throws an exception with all error messages');
            $this->is($e->getCode(), 'invalid password [invalid]', '->clean() throws an exception with all error messages');
        }

        $userValidator = new sfValidatorSchema(array(
            'left' => new sfValidatorString(array('min_length' => 2)),
            'right' => new sfValidatorString(array('min_length' => 2)),
            'password' => new sfValidatorString(array('min_length' => 2)),
            'password_bis' => new sfValidatorString(array('min_length' => 2)),
        ));
        $userValidator->setPostValidator(new sfValidatorAnd(array($comparator, $comparator1)));
        $v = new sfValidatorSchema(array(
            'left' => new sfValidatorString(array('min_length' => 2)),
            'right' => new sfValidatorString(array('min_length' => 2)),
            'password' => new sfValidatorString(array('min_length' => 2)),
            'password_bis' => new sfValidatorString(array('min_length' => 2)),
            'user' => $userValidator,
        ));
        $v->setPostValidator(new sfValidatorAnd(array($comparator, $comparator1)));
        try {
            $v->clean(array('left' => 'foo', 'right' => 'bar', 'password' => 'oof', 'password_bis' => 'rab', 'user' => array('left' => 'foo', 'right' => 'bar', 'password' => 'oof', 'password_bis' => 'rab')));
            $this->skip('', 7);
        } catch (sfValidatorErrorSchema $e) {
            $this->is(count($e->getNamedErrors()), 2, '->clean() throws an exception with all error messages');
            $this->is(count($e->getGlobalErrors()), 1, '->clean() throws an exception with all error messages');
            $this->is(count($e['user']->getNamedErrors()), 1, '->clean() throws an exception with all error messages');
            $this->is(count($e['user']->getGlobalErrors()), 1, '->clean() throws an exception with all error messages');
            $this->is(isset($e['user']) ? $e['user']->getCode() : '', 'invalid password [invalid]', '->clean() throws an exception with all error messages');
            $this->is(isset($e['user']['password']) ? $e['user']['password']->getCode() : '', 'invalid', '->clean() throws an exception with all error messages');
            $this->is($e->getCode(), 'invalid user [invalid password [invalid]] password [invalid]', '->clean() throws an exception with all error messages');
        }

        // __clone()
        $this->diag('__clone()');
        $v = new sfValidatorSchema(array('v1' => $v1, 'v2' => $v2));
        $v1 = clone $v;
        $f1 = $v1->getFields();
        $f = $v->getFields();
        $this->is(array_keys($f1), array_keys($f), '__clone() clones embedded validators');
        foreach ($f1 as $name => $validator) {
            $this->ok($validator !== $f[$name], '__clone() clones embedded validators');
            $this->ok($validator == $f[$name], '__clone() clones embedded validators');
        }
        $this->is($v1->getPreValidator(), null, '__clone() clones the pre validator');
        $this->is($v1->getPostValidator(), null, '__clone() clones the post validator');

        $v->setPreValidator(new sfValidatorString(array('min_length' => 4)));
        $v->setPostValidator(new sfValidatorString(array('min_length' => 4)));
        $v1 = clone $v;
        $this->ok($v1->getPreValidator() !== $v->getPreValidator(), '__clone() clones the pre validator');
        $this->ok($v1->getPreValidator() == $v->getPreValidator(), '__clone() clones the pre validator');
        $this->ok($v1->getPostValidator() !== $v->getPostValidator(), '__clone() clones the post validator');
        $this->ok($v1->getPostValidator() == $v->getPostValidator(), '__clone() clones the post validator');

        $this->diag('convert post_max_size to bytes');
        $v = new BytesValidatorSchema();
        $this->is($v->getBytes(null), 0, 'empty string considered as 0 bytes');
        $this->is($v->getBytes(''), 0, 'empty string considered as 0 bytes');
        $this->is($v->getBytes('0'), 0, 'simple bytes');
        $this->is($v->getBytes('1'), 1.0, 'simple bytes');
        $this->is($v->getBytes('1B'), 1.0, 'simple bytes');
        $this->is($v->getBytes('1K'), 1024.0, 'kilobytes');
        $this->is($v->getBytes('1M'), 1024.0 * 1024.0, 'megabytes short syntax');
        $this->is($v->getBytes('0.5M'), 1024.0 * 1024.0 / 2, 'fractional megabytes');
        $this->is($v->getBytes('1G'), 1024.0 * 1024.0 * 1024.0, 'gigabytes');
    }
}
