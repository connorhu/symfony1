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
class sfValidatorIntegerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v = new sfValidatorInteger();

        // ->clean()
        $this->diag('->clean()');
        $this->is($v->clean(12), 12, '->clean() returns the numbers unmodified');
        $this->is($v->clean('12'), 12, '->clean() converts strings to integers');

        try {
            $v->clean('not an integer');
            $this->fail('->clean() throws a sfValidatorError if the value is not an integer');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the value is not an integer');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        try {
            $v->clean(12.3);
            $this->fail('->clean() throws a sfValidatorError if the value is not an integer');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the value is not an integer');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        $v->setOption('required', false);
        $this->ok(null === $v->clean(null), '->clean() returns null for null values');

        $v->setOption('max', 2);
        $this->is($v->clean(1), 1, '->clean() checks the maximum number allowed');
        try {
            $v->clean(3);
            $this->fail('"max" option set the maximum number allowed');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('"max" option set the maximum number allowed');
            $this->is($e->getCode(), 'max', '->clean() throws a sfValidatorError');
        }

        $v->setMessage('max', 'Too large');
        try {
            $v->clean(5);
            $this->fail('"max" error message customization');
        } catch (sfValidatorError $e) {
            $this->is($e->getMessage(), 'Too large', '"max" error message customization');
        }

        $v->setOption('max', null);

        $v->setOption('min', 3);
        $this->is($v->clean(5), 5, '->clean() checks the minimum number allowed');
        try {
            $v->clean('1');
            $this->fail('"min" option set the minimum number allowed');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('"min" option set the minimum number allowed');
            $this->is($e->getCode(), 'min', '->clean() throws a sfValidatorError');
        }

        $v->setMessage('min', 'Too small');
        try {
            $v->clean(1);
            $this->fail('"min" error message customization');
        } catch (sfValidatorError $e) {
            $this->is($e->getMessage(), 'Too small', '"min" error message customization');
        }

        $v->setOption('min', null);
    }
}
