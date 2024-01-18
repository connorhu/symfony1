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
class sfValidatorTimeTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v = new sfValidatorTime();

        // ->clean()
        $this->diag('->clean()');

        $v->setOption('required', false);
        $this->ok(null === $v->clean(null), '->clean() returns null if not required');

        // validate strtotime formats
        $this->diag('validate strtotime formats');
        $this->is($v->clean('16:35:12'), '16:35:12', '->clean() accepts times parsable by strtotime');
        $this->is($v->clean('+1 hour'), date('H:i:s', time() + 3600), '->clean() accepts times parsable by strtotime');

        try {
            $v->clean('This is not a time');
            $this->fail('->clean() throws a sfValidatorError if the time is a string and is not parsable by strtotime');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the time is a string and is not parsable by strtotime');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        // validate timestamp
        $this->diag('validate timestamp');
        $this->is($v->clean(time()), date('H:i:s', time()), '->clean() accepts timestamps as input');

        // validate date array
        $this->diag('validate date array');
        $this->is($v->clean(array('hour' => 20, 'minute' => 10, 'second' => 15)), '20:10:15', '->clean() accepts an array as an input');
        $this->is($v->clean(array('hour' => '20', 'minute' => '10', 'second' => '15')), '20:10:15', '->clean() accepts an array as an input');
        $this->is($v->clean(array('hour' => '', 'minute' => '', 'second' => '')), null, '->clean() accepts an array as an input');
        $this->is($v->clean(array('hour' => 0, 'minute' => 0, 'second' => 0)), '00:00:00', '->clean() accepts an array as an input');
        $this->is($v->clean(array('hour' => '0', 'minute' => '0', 'second' => '0')), '00:00:00', '->clean() accepts an array as an input');

        try {
            $v->clean(array('hour' => '', 'minute' => 0, 'second' => 0));
            $this->fail('->clean() throws a sfValidatorError if time date is not valid');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the time is not valid');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        try {
            $v->clean(array('hour' => '', 'minute' => 1, 'second' => 15));
            $this->fail('->clean() throws a sfValidatorError if time date is not valid');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the time is not valid');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        try {
            $v->clean(array('hour' => -2, 'minute' => 1, 'second' => 15));
            $this->fail('->clean() throws a sfValidatorError if the time is not valid');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the time is not valid');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        // validate regex
        $this->diag('validate regex');
        $v->setOption('time_format', '~(?P<hour>\d{2})-(?P<minute>\d{2}).(?P<second>\d{2})~');
        $this->is($v->clean('20-10.18'), '20:10:18', '->clean() accepts a regular expression to match times');

        try {
            $v->clean('20.10-18');
            $this->fail('->clean() throws a sfValidatorError if the time does not match the regex');
            $this->skip('', 2);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the time does not match the regex');
            $this->like($e->getMessage(), '/'.preg_quote(htmlspecialchars($v->getOption('time_format'), ENT_QUOTES, 'UTF-8'), '/').'/', '->clean() returns the expected time format in the error message');
            $this->is($e->getCode(), 'bad_format', '->clean() throws a sfValidatorError');
        }

        $v->setOption('time_format_error', 'hh/mm/ss');
        try {
            $v->clean('20.10-18');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->like($e->getMessage(), '/'.preg_quote('hh/mm/ss', '/').'/', '->clean() returns the expected time format error if provided');
        }

        $v->setOption('time_format', null);

        // change date output
        $this->diag('change date output');
        $v->setOption('time_output', 'U');
        $this->is($v->clean(time()), time(), '->clean() output format can be change with the time_output option');

        // required
        $v = new sfValidatorTime();
        foreach (array(
            array('hour' => '', 'minute' => '', 'second' => ''),
            array('hour' => null, 'minute' => null, 'second' => null),
            '',
            null,
        ) as $input) {
            try {
                $v->clean($input);
                $this->fail('->clean() throws an exception if the time is empty and required is true');
            } catch (sfValidatorError $e) {
                $this->pass('->clean() throws an exception if the time is empty and required is true');
            }
        }
    }
}
