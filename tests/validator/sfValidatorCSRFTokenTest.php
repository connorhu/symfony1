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
class sfValidatorCSRFTokenTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        try {
            new sfValidatorCSRFToken();
            $this->fail('__construct() throws an RuntimeException if you don\'t pass a token option');
        } catch (RuntimeException $e) {
            $this->pass('__construct() throws an RuntimeException if you don\'t pass a token option');
        }

        $v = new sfValidatorCSRFToken(array('token' => 'symfony'));

        // ->clean()
        $this->diag('->clean()');
        $this->is($v->clean('symfony'), 'symfony', '->clean() checks that the token is valid');

        try {
            $v->clean('another');
            $this->fail('->clean() throws an sfValidatorError if the token is not valid');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the token is not valid');
            $this->is($e->getCode(), 'csrf_attack', '->clean() throws a sfValidatorError');
        }

        // ->asString()
        $this->diag('->asString()');
        $this->is($v->asString(), 'CSRFToken({ token: symfony })', '->asString() returns a string representation of the validator');
    }
}
