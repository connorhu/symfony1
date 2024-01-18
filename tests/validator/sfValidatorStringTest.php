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
class sfValidatorStringTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v = new sfValidatorString();
        $v::setCharset('UTF-8');

        // ->clean()
        $this->diag('->clean()');
        $this->is($v->clean('foo'), 'foo', '->clean() returns the string unmodified');

        $v->setOption('required', false);
        $this->ok('' === $v->clean(null), '->clean() converts the value to a string');
        $this->ok('1' === $v->clean(1), '->clean() converts the value to a string');

        $v->setOption('max_length', 2);
        $this->is($v->clean('fo'), 'fo', '->clean() checks the maximum length allowed');
        try {
            $v->clean('foo');
            $this->fail('"max_length" option set the maximum length of the string');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('"max_length" option set the maximum length of the string');
            $this->is($e->getCode(), 'max_length', '->clean() throws a sfValidatorError');
        }

        $v->setMessage('max_length', 'Too long');
        try {
            $v->clean('foo');
            $this->fail('"max_length" error message customization');
        } catch (sfValidatorError $e) {
            $this->is($e->getMessage(), 'Too long', '"max_length" error message customization');
        }

        $v->setOption('max_length', null);

        $v->setOption('min_length', 3);
        $this->is($v->clean('foo'), 'foo', '->clean() checks the minimum length allowed');
        try {
            $v->clean('fo');
            $this->fail('"min_length" option set the minimum length of the string');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('"min_length" option set the minimum length of the string');
            $this->is($e->getCode(), 'min_length', '->clean() throws a sfValidatorError');
        }

        $v->setMessage('min_length', 'Too short');
        try {
            $v->clean('fo');
            $this->fail('"min_length" error message customization');
        } catch (sfValidatorError $e) {
            $this->is($e->getMessage(), 'Too short', '"min_length" error message customization');
        }

        $v->setOption('min_length', null);

        $this->diag('UTF-8 support');
        $v->setOption('max_length', 4);
        $this->is($v->clean('été'), 'été', '"sfValidatorString" supports UTF-8');
    }
}
