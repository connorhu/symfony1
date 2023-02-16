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
class sfValidatorErrorTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function will_crash($a)
    {
        return serialize(new sfValidatorError(new sfValidatorString(), 'max_length', array('value' => 'foo<br />', 'max_length' => 1)));
    }

    public function testTodoMigrate()
    {
        $v = new sfValidatorString();

        $e = new sfValidatorError($v, 'max_length', array('value' => 'foo<br />', 'max_length' => 1));

        // ->getValue()
        $this->diag('->getValue()');
        $this->is($e->getValue(), 'foo<br />', '->getValue() returns the value that has been validated with the validator');

        $e1 = new sfValidatorError($v, 'max_length', array('max_length' => 1));
        $this->is($e1->getValue(), null, '->getValue() returns null if there is no value key in arguments');

        // ->getValidator()
        $this->diag('->getValidator()');
        $this->is($e->getValidator(), $v, '->getValidator() returns the validator that triggered this exception');

        // ->getArguments()
        $this->diag('->getArguments()');
        $this->is($e->getArguments(), array('%value%' => 'foo&lt;br /&gt;', '%max_length%' => '1'), '->getArguments() returns the arguments needed to format the error message, escaped according to the current charset');
        $this->is($e->getArguments(true), array('value' => 'foo<br />', 'max_length' => 1), '->getArguments() takes a Boolean as its first argument to return the raw arguments');

        // ->getMessageFormat()
        $this->diag('->getMessageFormat()');
        $this->is($e->getMessageFormat(), $v->getMessage($e->getCode()), '->getMessageFormat()');

        // ->getMessage()
        $this->diag('->getMessage()');
        $this->is($e->getMessage(), '"foo&lt;br /&gt;" is too long (1 characters max).', '->getMessage() returns the error message string');

        // ->getCode()
        $this->diag('->getCode()');
        $this->is($e->getCode(), 'max_length', '->getCode() returns the error code');

        // ->__toString()
        $this->diag('__toString()');
        $this->is($e->__toString(), $e->getMessage(), '->__toString() returns the error message string');

        // implements Serializable
        $this->diag('implements Serializable');

        $a = new NotSerializable();

        try {
            $serialized = $this->will_crash($a);
            $this->pass('sfValidatorError implements Serializable');
        } catch (Exception $e) {
            $this->fail('sfValidatorError implements Serializable');
        }

        $e1 = unserialize($serialized);
        $this->is($e1->getMessage(), $e->getMessage(), 'sfValidatorError implements Serializable');
        $this->is($e1->getCode(), $e->getCode(), 'sfValidatorError implements Serializable');
        $this->is(get_class($e1->getValidator()), get_class($e->getValidator()), 'sfValidatorError implements Serializable');
        $this->is($e1->getArguments(), $e->getArguments(), 'sfValidatorError implements Serializable');
    }
}
