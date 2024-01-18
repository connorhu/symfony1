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
class sfValidatorDateRangeTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        try {
            new sfValidatorDateRange();
            $this->fail('__construct() throws a sfValidatorError if you don\'t pass a from_date and a to_date option');
            $this->skip('', 1);
        } catch (RuntimeException $e) {
            $this->pass('__construct() throws a RuntimeException if you don\'t pass a from_date and a to_date option');
        }

        $v = new sfValidatorDateRange(array(
            'from_date' => new sfValidatorDate(array('required' => false)),
            'to_date' => new sfValidatorDate(array('required' => false)),
        ));

        // ->clean()
        $this->diag('->clean()');

        $values = $v->clean(array('from' => '2008-01-01', 'to' => '2009-01-01'));
        $this->is($values, array('from' => '2008-01-01', 'to' => '2009-01-01'), '->clean() returns the from and to values');

        try {
            $v->clean(array('from' => '2008-01-01', 'to' => '1998-01-01'));
            $this->fail('->clean() throws a sfValidatorError if the from date is after the to date');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the from date is after the to date');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        // custom field names
        $this->diag('Custom field names options');

        $v = new sfValidatorDateRange(array(
            'from_date' => new sfValidatorDate(array('required' => true)),
            'to_date' => new sfValidatorDate(array('required' => true)),
            'from_field' => 'custom_from',
            'to_field' => 'custom_to',
        ));

        try {
            $v->clean(array('from' => '2008-01-01', 'to' => '1998-01-01'));
            $this->fail('->clean() take into account custom fields');
        } catch (sfValidatorError $e) {
            $this->pass('->clean() take into account custom fields');
        }

        $values = $v->clean(array('custom_from' => '2008-01-01', 'custom_to' => '2009-01-01'));
        $this->is($values, array('custom_from' => '2008-01-01', 'custom_to' => '2009-01-01'), '->clean() returns the from and to values for custom field names');
    }
}
