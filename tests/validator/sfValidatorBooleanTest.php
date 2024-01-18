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
require_once __DIR__.'/../fixtures/MyFalseClass.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfValidatorBooleanTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v = new sfValidatorBoolean();

        // ->clean()
        $this->diag('->clean()');

        // true values
        $this->diag('true values');
        foreach ($v->getOption('true_values') as $true_value) {
            $this->is($v->clean($true_value), true, '->clean() returns true if the value is in the true_values option');
        }

        // false values
        $this->diag('false values');
        foreach ($v->getOption('false_values') as $false_value) {
            $this->is($v->clean($false_value), false, '->clean() returns false if the value is in the false_values option');
        }

        // other special test cases
        $this->is($v->clean(0), false, '->clean() returns false if the value is 0');
        $this->is($v->clean(false), false, '->clean() returns false if the value is false');
        $this->is($v->clean(1), true, '->clean() returns true if the value is 1');
        $this->is($v->clean(true), true, '->clean() returns true if the value is true');
        $this->is($v->clean(''), false, '->clean() returns false if the value is empty string as empty_value is false by default');

        $this->is($v->clean(new MyFalseClass()), false, '->clean() returns false if the value is false');

        // required is false by default
        $this->is($v->clean(null), false, '->clean() returns false if the value is null');

        try {
            $v->clean('astring');
            $this->fail('->clean() throws an error if the input value is not a true or a false value');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an error if the input value is not a true or a false value');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        // empty
        $this->diag('empty');
        $v->setOption('required', false);
        $this->ok(false === $v->clean(null), '->clean() returns false if no value is given');
        $v->setOption('empty_value', true);
        $this->ok(true === $v->clean(null), '->clean() returns the value of the empty_value option if no value is given');
    }
}
