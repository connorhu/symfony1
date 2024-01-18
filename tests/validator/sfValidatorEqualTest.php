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
class sfValidatorEqualTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        try {
            new sfValidatorEqual();
            $this->fail('->__construct() throws an "RuntimeException" if you don\'t pass a "value" option');
        } catch (RuntimeException $e) {
            $this->pass('->__construct() throws an "RuntimeException" if you don\'t pass a "value" option');
        }

        $v = new sfValidatorEqual(array('value' => 'foo'));

        // ->clean()
        $this->diag('->clean()');
        $this->is($v->clean('foo'), 'foo', '->clean() returns the value unmodified');

        $v->setOption('value', '0');
        $this->ok(0 === $v->clean(0), '->clean() returns the value unmodified');

        try {
            $v->clean('bar');
            $this->fail('->clean() fails values are not equal');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() fails values are not equal');
            $this->is($e->getCode(), 'not_equal', '->clean() throws a sfValidatorError');
        }

        $v->setMessage('not_equal', 'Not equal');
        try {
            $v->clean('bar');
            $this->fail('"not_equal" error message customization');
        } catch (sfValidatorError $e) {
            $this->is($e->getMessage(), 'Not equal', '"not_equal" error message customization');
        }

        $v->setOption('strict', true);
        $v->setOption('value', '0');

        try {
            $v->clean(0);
            $this->fail('"strict" option set the operator for comparaison');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('"strict" option set the operator for comparaison');
            $this->is($e->getCode(), 'not_strictly_equal', '->clean() throws a sfValidatorError');
        }

        $v->setMessage('not_strictly_equal', 'Not strictly equal');
        try {
            $v->clean(0);
            $this->fail('"not_strictly_equal" error message customization');
        } catch (sfValidatorError $e) {
            $this->is($e->getMessage(), 'Not strictly equal', '"not_strictly_equal" error message customization');
        }
    }
}
