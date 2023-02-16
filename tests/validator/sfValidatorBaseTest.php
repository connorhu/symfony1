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
require_once __DIR__.'/../fixtures/ValidatorIdentity.php';
require_once __DIR__.'/../fixtures/ValidatorIdentityWithRequired.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfValidatorBaseTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->configure()
        $this->diag('->configure()');
        $v = new ValidatorIdentity();
        $this->is($v->getOption('foo'), 'bar', '->configure() can add some options');
        $v = new ValidatorIdentity(array('foo' => 'foobar'));
        $this->is($v->getOption('foo'), 'foobar', '->configure() takes an options array as its first argument and values override default option values');
        $v = new ValidatorIdentity();
        $this->is($v->getMessage('foo'), 'bar', '->configure() can add some message');
        $v = new ValidatorIdentity(array(), array('foo' => 'foobar'));
        $this->is($v->getMessage('foo'), 'foobar', '->configure() takes a messages array as its second argument and values override default message values');

        try {
            new ValidatorIdentity(array('nonexistant' => false, 'foo' => 'foobar', 'anothernonexistant' => 'bar', 'required' => true));
            $this->fail('__construct() throws an InvalidArgumentException if you pass some non existant options');
            $this->skip();
        } catch (InvalidArgumentException $e) {
            $this->pass('__construct() throws an InvalidArgumentException if you pass some non existant options');
            $this->like($e->getMessage(), '/ \'nonexistant\', \'anothernonexistant\'/', 'The exception contains the non existant option names');
        }

        try {
            new ValidatorIdentity(array(), array('required' => 'This is required.', 'nonexistant' => 'foo', 'anothernonexistant' => false));
            $this->fail('__construct() throws an InvalidArgumentException if you pass some non existant error codes');
            $this->skip();
        } catch (InvalidArgumentException $e) {
            $this->pass('__construct() throws an InvalidArgumentException if you pass some non existant error codes');
            $this->like($e->getMessage(), '/ \'nonexistant\', \'anothernonexistant\'/', 'The exception contains the non existant error codes');
        }

        // ->getRequiredOptions()
        $this->diag('getRequiredOptions');
        $v = new ValidatorIdentityWithRequired(array('foo' => 'bar'));
        $this->is($v->getRequiredOptions(), array('foo'), '->getRequiredOptions() returns an array of required option names');

        try {
            new ValidatorIdentityWithRequired();
            $this->fail('__construct() throws an RuntimeException if you don\'t pass a required option');
        } catch (RuntimeException $e) {
            $this->pass('__construct() throws an RuntimeException if you don\'t pass a required option');
        }

        $v = new ValidatorIdentity();

        // ->clean()
        $this->diag('->clean()');
        $this->is($v->clean('foo'), 'foo', '->clean() returns a cleanup version of the data to validate');
        try {
            $this->is($v->clean(''), '');
            $this->fail('->clean() throws a sfValidatorError exception if the data does not validate');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError exception if the data does not validate');
            $this->is($e->getCode(), 'required', '->clean() throws a sfValidatorError');
        }
        $this->is($v->clean('  foo  '), 'foo', '->clean() trim whitespaces by default');

        // ->isEmpty()
        $this->diag('->isEmpty()');
        $this->is($v->testIsEmpty(null), true, 'null value isEmpty()');
        $this->is($v->testIsEmpty(''), true, 'empty string value isEmpty()');
        $this->is($v->testIsEmpty(array()), true, 'empty array value isEmpty()');
        $this->is($v->testIsEmpty(false), false, 'false value not isEmpty()');

        // ->getEmptyValue()
        $this->diag('->getEmptyValue()');
        $v->setOption('required', false);
        $v->setOption('empty_value', 'defaultnullvalue');
        $this->is($v->clean(''), 'defaultnullvalue', '->getEmptyValue() returns the representation of an empty value for this validator');
        $v->setOption('empty_value', null);

        // ->setOption()
        $this->diag('->setOption()');
        $v->setOption('required', false);
        $this->is($v->clean(''), null, '->setOption() changes options (required for example)');
        $v->setOption('trim', true);
        $this->is($v->clean('  foo  '), 'foo', '->setOption() can turn on whitespace trimming');
        try {
            $v->setOption('foobar', 'foo');
            $this->fail('->setOption() throws an InvalidArgumentException if the option is not registered');
        } catch (InvalidArgumentException $e) {
            $this->pass('->setOption() throws an InvalidArgumentException if the option is not registered');
        }

        // ->hasOption()
        $this->diag('->hasOption()');
        $this->ok($v->hasOption('required'), '->hasOption() returns true if the validator has the option');
        $this->ok(!$v->hasOption('nonexistant'), '->hasOption() returns false if the validator does not have the option');

        // ->getOption()
        $this->diag('->getOption()');
        $this->is($v->getOption('required'), false, '->getOption() returns the value of an option');
        $this->is($v->getOption('nonexistant'), null, '->getOption() returns null if the option does not exist');

        // ->addOption()
        $this->diag('->addOption()');
        $v->addOption('foobar');
        $v->setOption('foobar', 'foo');
        $this->is($v->getOption('foobar'), 'foo', '->addOption() adds a new option to a validator');

        // ->getOptions() ->setOptions()
        $this->diag('->getOptions() ->setOptions()');
        $v->setOptions(array('required' => true, 'trim' => false));
        $this->is($v->getOptions(), array('required' => true, 'trim' => false, 'empty_value' => null), '->setOptions() changes all options');

        // ->getMessages()
        $this->diag('->getMessages()');
        $this->is($v->getMessages(), array('required' => 'Required.', 'invalid' => 'Invalid.', 'foo' => 'bar'), '->getMessages() returns an array of all error messages');

        // ->getMessage()
        $this->diag('->getMessage()');
        $this->is($v->getMessage('required'), 'Required.', '->getMessage() returns an error message string');
        $this->is($v->getMessage('nonexistant'), '', '->getMessage() returns an empty string if the message does not exist');

        // ->setMessage()
        $this->diag('->setMessage()');
        $v->setMessage('required', 'The field is required.');
        try {
            $v->clean('');
            $this->isnt($e->getMessage(), 'The field is required.', '->setMessage() changes the default error message string');
        } catch (sfValidatorError $e) {
            $this->is($e->getMessage(), 'The field is required.', '->setMessage() changes the default error message string');
        }

        try {
            $v->setMessage('foobar', 'foo');
            $this->fail('->setMessage() throws an InvalidArgumentException if the message is not registered');
        } catch (InvalidArgumentException $e) {
            $this->pass('->setMessage() throws an InvalidArgumentException if the message is not registered');
        }

        // ->setMessages()
        $this->diag('->setMessages()');
        $v->setMessages(array('required' => 'This is required!'));
        $this->is($v->getMessages(), array('required' => 'This is required!', 'invalid' => 'Invalid.'), '->setMessages() changes all error messages');

        // ->addMessage()
        $this->diag('->addMessage()');
        $v->addMessage('foobar', 'foo');
        $v->setMessage('foobar', 'bar');
        $this->is($v->getMessage('foobar'), 'bar', '->addMessage() adds a new error code');

        // ->getErrorCodes()
        $this->diag('->getErrorCodes()');
        $this->is($v->getErrorCodes(), array('required', 'invalid', 'foo'), '->getErrorCodes() returns an array of error codes the validator can use');

        // ::getCharset() ::setCharset()
        $this->diag('::getCharset() ::setCharset()');
        sfValidatorBase::setCharset('UTF-78');
        $this->is(sfValidatorBase::getCharset(), 'UTF-78', '::getCharset() returns the charset to use for validators');
        sfValidatorBase::setCharset('ISO-8859-1');
        $this->is(sfValidatorBase::getCharset(), 'ISO-8859-1', '::setCharset() changes the charset to use for validators');

        // ->asString()
        $this->diag('->asString()');
        $v = new ValidatorIdentity();
        $this->is($v->asString(), 'ValidatorIdentity()', '->asString() returns a string representation of the validator');
        $v->setOption('required', false);
        $v->setOption('foo', 'foo');
        $this->is($v->asString(), 'ValidatorIdentity({ required: false, foo: foo })', '->asString() returns a string representation of the validator');

        $v->setMessage('required', 'This is required.');
        $this->is($v->asString(), 'ValidatorIdentity({ required: false, foo: foo }, { required: \'This is required.\' })', '->asString() returns a string representation of the validator');

        $v = new ValidatorIdentity();
        $v->setMessage('required', 'This is required.');
        $this->is($v->asString(), 'ValidatorIdentity({}, { required: \'This is required.\' })', '->asString() returns a string representation of the validator');

        // ::setDefaultMessage()
        $this->diag('::setDefaultMessage()');
        ValidatorIdentity::setDefaultMessage('required', 'This field is required.');
        ValidatorIdentity::setDefaultMessage('invalid', 'This field is invalid.');
        ValidatorIdentity::setDefaultMessage('foo', 'Foo bar.');
        $v = new ValidatorIdentity();
        $this->is($v->getMessage('required'), 'This field is required.', '::setDefaultMessage() sets the default message for an error');
        $this->is($v->getMessage('invalid'), 'This field is invalid.', '::setDefaultMessage() sets the default message for an error');
        $this->is($v->getMessage('foo'), 'Foo bar.', '::setDefaultMessage() sets the default message for an error');

        $v = new ValidatorIdentity(array(), array('required' => 'Yep, this is required!', 'foo' => 'Yep, this is a foo error!'));
        $this->is($v->getMessage('required'), 'Yep, this is required!', '::setDefaultMessage() is ignored if the validator explicitly overrides the message');
        $this->is($v->getMessage('foo'), 'Yep, this is a foo error!', '::setDefaultMessage() is ignored if the validator explicitly overrides the message');
    }
}
