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
class sfValidatorSchemaFilterTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v1 = new sfValidatorString(array('min_length' => 2, 'trim' => true));

        $v = new sfValidatorSchemaFilter('first_name', $v1);

        // ->clean()
        $this->diag('->clean()');
        $this->is($v->clean(array('first_name' => '  foo  ')), array('first_name' => 'foo'), '->clean() executes the embedded validator');

        try {
            $v->clean('string');
            $this->fail('->clean() throws a InvalidArgumentException if the input value is not an array');
        } catch (InvalidArgumentException $e) {
            $this->pass('->clean() throws a InvalidArgumentException if the input value is not an array');
        }

        try {
            $v->clean(null);
            $this->fail('->clean() throws a sfValidatorError if the embedded validator failed');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the embedded validator failed');
            $this->is($e->getCode(), 'first_name [required]', '->clean() throws a sfValidatorError');
        }

        try {
            $v->clean(array('first_name' => 'f'));
            $this->fail('->clean() throws a sfValidatorError if the embedded validator failed');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the embedded validator failed');
            $this->is($e->getCode(), 'first_name [min_length]', '->clean() throws a sfValidatorError');
        }
    }
}
