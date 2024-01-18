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
class sfValidatorDateTimeTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v = new sfValidatorDateTime();

        $this->ok($v instanceof sfValidatorDate, 'sfValidatorDateTime extends sfValidatorDate');

        // with_time option
        $this->diag('with_time option');
        $this->is($v->clean(time()), date('Y-m-d H:i:s', time()), '->clean() validates date with time');
        $this->is($v->clean(array('year' => 2005, 'month' => 1, 'day' => 4, 'hour' => 2, 'minute' => 23, 'second' => 33)), '2005-01-04 02:23:33', '->clean() validates date with time');
        $this->is($v->clean('1855-08-25 13:22:56'), '1855-08-25 13:22:56', '->clean() validates date with time');
    }
}
