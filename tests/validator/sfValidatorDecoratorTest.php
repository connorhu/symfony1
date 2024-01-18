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
require_once __DIR__.'/../fixtures/MyValidator.php';
require_once __DIR__.'/../fixtures/FakeValidator.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfValidatorDecoratorTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        $v = new MyValidator(array('required' => false, 'empty_value' => null));
        $this->is($v->clean(null), null, '__construct() options override the embedded validator options');
        $v = new MyValidator(array(), array('required' => 'This is required.'));
        try {
            $v->clean(null);
            $this->fail('->clean() throws a sfValidatorError if the value is required');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the value is required');
            $this->is($e->getMessage(), 'This is required.', '__construct() messages override the embedded validator messages');
        }

        $v = new MyValidator();

        // ->getErrorCodes()
        $this->diag('->getErrorCodes()');
        $this->is($v->getErrorCodes(), $v->getValidator()->getErrorCodes(), '->getErrorCodes() is a proxy to the embedded validator method');

        // ->asString()
        $this->diag('->asString()');
        $this->is($v->asString(), $v->getValidator()->asString(), '->asString() is a proxy to the embedded validator method');

        // ->getDefaultMessages()
        $this->diag('->getDefaultMessages()');
        $this->is($v->getDefaultMessages(), $v->getValidator()->getDefaultMessages(), '->getDefaultMessages() is a proxy to the embedded validator method');

        // ->getDefaultOptions()
        $this->diag('->getDefaultOptions()');
        $this->is($v->getDefaultOptions(), $v->getValidator()->getDefaultOptions(), '->getDefaultOptions() is a proxy to the embedded validator method');

        // ->getMessage() ->getMessages() ->setMessage() ->setMessages()
        $this->diag('->getMessage() ->getMessages() ->setMessage() ->setMessages()');
        $v = new MyValidator();
        $this->is($v->getMessage('required'), 'This string is required.', '->getMessage() returns a message from the embedded validator');
        $v->setMessage('invalid', 'This string is invalid.');
        $this->is($v->getMessages(), array('required' => 'This string is required.', 'invalid' => 'This string is invalid.', 'max_length' => '"%value%" is too long (%max_length% characters max).', 'min_length' => '"%value%" is too short (%min_length% characters min).'), '->getMessages() returns messages from the embedded validator');
        $v->setMessages(array('required' => 'Required...'));
        $this->is($v->getMessages(), array('required' => 'Required...', 'invalid' => 'This field is invalid.'), '->setMessages() sets all messages for the embedded validator');

        // ->getOption() ->getOptions() ->hasOption() ->getOptions() ->setOptions()
        $v = new MyValidator();
        $this->is($v->getOption('trim'), true, '->getOption() returns an option from the embedded validator');
        $v->setOption('trim', false);
        $this->is($v->getOptions(), array('required' => true, 'trim' => false, 'empty_value' => '', 'max_length' => null, 'min_length' => 2), '->getOptions() returns an array of options from the embedded validator');
        $this->is($v->hasOption('min_length'), true, '->hasOption() returns true if the embedded validator has a given option');
        $v->setOptions(array('min_length' => 10));
        $this->is($v->getOptions(), array('required' => true, 'trim' => false, 'empty_value' => null, 'min_length' => 10), '->setOptions() sets all options for the embedded validator');

        $v = new MyValidator();

        // ->clean()
        $this->diag('->clean()');
        try {
            $v->clean(null);
            $this->fail('->clean() throws a sfValidatorError if the value is required');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the value is required');
            $this->is($e->getCode(), 'required', '->clean() throws a sfValidatorError');
        }

        try {
            $v->clean('f');
            $this->fail('->clean() throws a sfValidatorError if the wrapped validator failed');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the wrapped validator failed');
            $this->is($e->getCode(), 'min_length', '->clean() throws a sfValidatorError');
        }

        $this->is($v->clean('  foo  '), 'foo', '->clean() cleans the value by executing the clean() method from the wrapped validator');

        try {
            $v = new FakeValidator();
            $this->fail('->clean() throws a RuntimeException if getValidator() does not return a sfValidator instance');
        } catch (RuntimeException $e) {
            $this->pass('->clean() throws a RuntimeException if getValidator() does not return a sfValidator instance');
        }
    }
}
