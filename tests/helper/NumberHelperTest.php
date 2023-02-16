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
require_once __DIR__.'/../../lib/helper/NumberHelper.php';

/**
 * @internal
 *
 * @coversNothing
 */
class NumberHelperTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // format_number()
        $this->diag('format_number()');
        $this->is(format_number(10012.1, 'en'), '10,012.1', 'format_number() takes a number as its first argument');
        // $this->is(format_number(10012.1, 'fr'), '10.012,1', 'format_number() takes a culture as its second argument');

        // TODO format_number() takes the current user culture if no second argument is given

        // format_currency()
        $this->is(format_currency(1200000.00, 'USD', 'en'), '$1,200,000.00', 'format_currency() takes a number as its first argument');
        $this->is(format_currency(1200000.1, 'USD', 'en'), '$1,200,000.10', 'format_currency() takes a number as its first argument');
        $this->is(format_currency(1200000.10, 'USD', 'en'), '$1,200,000.10', 'format_currency() takes a number as its first argument');
        $this->is(format_currency(1200000.101, 'USD', 'en'), '$1,200,000.10', 'format_currency() takes a number as its first argument');
        $this->is(format_currency('1200000', 'USD', 'en'), '$1,200,000.00', 'format_currency() takes a number as its first argument');

        $this->is(format_currency(-1200000, 'USD', 'en'), '($1,200,000.00)', 'format_currency() takes a number as its first argument');
        $this->is(format_currency(-1200000, 'USD', 'en_GB'), '-$1,200,000.00', 'format_currency() takes a number as its first argument');
        // $this->is(format_currency(1200000, 'USD', 'de'), '1.200.000,00 $', 'format_currency() takes a number as its first argument');
        // $this->is(format_currency(-1200000, 'USD', 'de'), '-1.200.000,00 $', 'format_currency() takes a number as its first argument');

        $this->is(format_currency('11.50999', 'USD', 'en'), '$11.50', 'format_currency() takes a number as its first argument');
        $this->is(format_currency('11.50999', 'EUR', 'fr'), '11,50 €', 'format_currency() takes a number as its first argument');
        $this->is(format_currency('11.9999464', 'EUR', 'fr'), '11,99 €', 'format_currency() takes a number as its first argument');
    }
}
