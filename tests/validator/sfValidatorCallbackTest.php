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
class sfValidatorCallbackTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function clean_test($validator, $value, $arguments)
    {
        if ('foo' != $value) {
            throw new sfValidatorError($validator, 'must_be_foo');
        }

        return "*{$value}*".implode('-', $arguments);
    }

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        try {
            new sfValidatorCallback();
            $this->fail('__construct() throws an RuntimeException if you don\'t pass a callback option');
        } catch (RuntimeException $e) {
            $this->pass('__construct() throws an RuntimeException if you don\'t pass a callback option');
        }

        $v = new sfValidatorCallback(array('callback' => array($this, 'clean_test')));

        // ->configure()
        $this->diag('->configure()');
        $this->is($v->clean(''), null, '->configure() switch required to false by default');

        // ->clean()
        $this->diag('->clean()');
        $this->is($v->clean('foo'), '*foo*', '->clean() calls our validator callback');
        try {
            $v->clean('bar');
            $this->fail('->clean() throws a sfValidatorError');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError');
            $this->is($e->getCode(), 'must_be_foo', '->clean() throws a sfValidatorError');
        }

        $this->diag('callback with arguments');
        $v = new sfValidatorCallback(array('callback' => array($this, 'clean_test'), 'arguments' => array('fabien', 'symfony')));
        $this->is($v->clean('foo'), '*foo*fabien-symfony', '->configure() can take an arguments option');

        // ->asString()
        $this->diag('->asString()');
        $v = new sfValidatorCallback(array('callback' => 'clean_test'));
        $this->is($v->asString(), 'Callback({ callback: clean_test })', '->asString() returns a string representation of the validator');
    }
}
