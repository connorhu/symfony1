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
class sfValidatorDateTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v = new sfValidatorDate();

        // ->clean()
        $this->diag('->clean()');

        $v->setOption('required', false);
        $this->ok(null === $v->clean(null), '->clean() returns null if not required');

        // validate strtotime formats
        $this->diag('validate strtotime formats');
        $this->is($v->clean('18 october 2005'), '2005-10-18', '->clean() accepts dates parsable by strtotime');
        $this->is($v->clean('+1 day'), date('Y-m-d', time() + 86400), '->clean() accepts dates parsable by strtotime');

        try {
            $v->clean('This is not a date');
            $this->fail('->clean() throws a sfValidatorError if the date is a string and is not parsable by strtotime');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the date is a string and is not parsable by strtotime');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        // validate timestamp
        $this->diag('validate timestamp');
        $this->is($v->clean(time()), date('Y-m-d', time()), '->clean() accepts timestamps as input');

        // validate date array
        $this->diag('validate date array');
        $this->is($v->clean(array('year' => 2005, 'month' => 10, 'day' => 15)), '2005-10-15', '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => '2005', 'month' => '10', 'day' => '15')), '2005-10-15', '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => '', 'month' => '', 'day' => '')), null, '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => 2008, 'month' => 02, 'day' => 29)), '2008-02-29', '->clean() recognises a leapyear');

        try {
            $v->clean(array('year' => '', 'month' => 1, 'day' => 15));
            $this->fail('->clean() throws a sfValidatorError if the date is not valid');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the date is not valid');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        try {
            $v->clean(array('year' => -2, 'month' => 1, 'day' => 15));
            $this->fail('->clean() throws a sfValidatorError if the date is not valid');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the date is not valid');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        try {
            $v->clean(array('year' => 2008, 'month' => 2, 'day' => 30));
            $this->fail('->clean() throws a sfValidatorError if the date is not valid');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the date is not valid');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        // validate regex
        $this->diag('validate regex');
        $v->setOption('date_format', '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~');
        $this->is($v->clean('18/10/2005'), '2005-10-18', '->clean() accepts a regular expression to match dates');
        $this->is($v->clean(array('year' => '2005', 'month' => '10', 'day' => '18')), '2005-10-18', '->clean() accepts a regular expression when cleaning an array');

        try {
            $v->clean('2005-10-18');
            $this->fail('->clean() throws a sfValidatorError if the date does not match the regex');
            $this->skip('', 2);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the date does not match the regex');
            $this->like($e->getMessage(), '/'.preg_quote(htmlspecialchars($v->getOption('date_format'), ENT_QUOTES, 'UTF-8'), '/').'/', '->clean() returns the expected date format in the error message');
            $this->is($e->getCode(), 'bad_format', '->clean() throws a sfValidatorError');
        }

        $v->setOption('date_format_error', 'dd/mm/YYYY');
        try {
            $v->clean('2005-10-18');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->like($e->getMessage(), '/'.preg_quote('dd/mm/YYYY', '/').'/', '->clean() returns the expected date format error if provided');
        }

        $v->setOption('date_format', null);

        // option with_time
        $this->diag('option with_time');
        $v->setOption('with_time', true);
        $this->is($v->clean(array('year' => 2005, 'month' => 10, 'day' => 15, 'hour' => 12, 'minute' => 10, 'second' => 15)), '2005-10-15 12:10:15', '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => '2005', 'month' => '10', 'day' => '15', 'hour' => '12', 'minute' => '10', 'second' => '15')), '2005-10-15 12:10:15', '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => '', 'month' => '', 'day' => '', 'hour' => '', 'minute' => '', 'second' => '')), null, '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => 2005, 'month' => 10, 'day' => 15, 'hour' => 12, 'minute' => 10, 'second' => '')), '2005-10-15 12:10:00', '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => 2005, 'month' => 10, 'day' => 15, 'hour' => 12, 'minute' => 10)), '2005-10-15 12:10:00', '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => 2005, 'month' => 10, 'day' => 15, 'hour' => 0, 'minute' => 10)), '2005-10-15 00:10:00', '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => 2005, 'month' => 10, 'day' => 15, 'hour' => '0', 'minute' => 10)), '2005-10-15 00:10:00', '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => 2005, 'month' => 10, 'day' => 15, 'hour' => 10)), '2005-10-15 10:00:00', '->clean() accepts an array as an input');
        $this->is($v->clean(array('year' => 2005, 'month' => 10, 'day' => 15, 'hour' => 0)), '2005-10-15 00:00:00', '->clean() accepts an array as an input');
        try {
            $v->clean(array('year' => 2005, 'month' => 1, 'day' => 15, 'hour' => 12, 'minute' => '', 'second' => 12));
            $this->fail('->clean() throws a sfValidatorError if the time is not valid');
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws a sfValidatorError if the time is not valid');
        }

        $this->is($v->clean('18 october 2005 12:30'), '2005-10-18 12:30:00', '->clean() can accept date time with the with_time option');
        $this->is($v->clean(time()), date('Y-m-d H:i:s', time()), '->clean() can accept date time with the with_time option');
        $v->setOption('date_format', '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~');
        $this->is($v->clean('18/10/2005'), '2005-10-18 00:00:00', '->clean() can accept date time with the with_time option');
        $v->setOption('date_format', '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4}) (?P<hour>\d{2})\:(?P<minute>\d{2})~');
        $this->is($v->clean('18/10/2005 12:30'), '2005-10-18 12:30:00', '->clean() can accept date time with the with_time option');
        $v->setOption('date_format', null);

        // change date output
        $this->diag('change date output');
        $v->setOption('with_time', false);
        $v->setOption('date_output', 'U');
        $time = time();
        $this->is($v->clean($time), $time, '->clean() output format can be change with the date_output option');

        $v->setOption('datetime_output', 'U');
        $v->setOption('with_time', true);
        $time = time();
        $this->is($v->clean($time), $time, '->clean() output format can be change with the date_output option');

        // required
        $v = new sfValidatorDate();
        foreach (array(
            array('year' => '', 'month' => '', 'day' => ''),
            array('year' => null, 'month' => null, 'day' => null),
            '',
            null,
        ) as $input) {
            try {
                $v->clean($input);
                $this->fail('->clean() throws an exception if the date is empty and required is true');
            } catch (sfValidatorError $e) {
                $this->pass('->clean() throws an exception if the date is empty and required is true');
            }
        }

        // max and min options
        $this->diag('max and min options');
        $v->setOption('min', strtotime('1 Jan 2005'));
        $v->setOption('max', strtotime('31 Dec 2007'));
        $this->is($v->clean('18 october 2005'), '2005-10-18', '->clean() can accept a max/min option');
        try {
            $v->clean('18 october 2004');
            $this->fail('->clean() throws an exception if the date is not within the range provided by the min/max options');
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an exception if the date is not within the range provided by the min/max options');
        }
        try {
            $v->clean('18 october 2008');
            $this->fail('->clean() throws an exception if the date is not within the range provided by the min/max options');
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an exception if the date is not within the range provided by the min/max options');
        }

        // max and min options out off timestamp range
        $this->diag('max and min options out off timestamp range');
        $v->setOption('min', '1805-12-31 10:00:00');
        $v->setOption('max', '2107-12-31 10:50:00');
        $this->is($v->clean('18 october 2105'), '2105-10-18', '->clean() can accept a max/min option string');
        $this->is($v->clean(array('year' => 1906, 'month' => 2, 'day' => 13)), '1906-02-13', '->clean() can accept a max/min option array');
        try {
            $v->clean('18 october 1804');
            $this->fail('->clean() throws an exception if the date is not within the range provided by the min/max options');
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an exception if the date is not within the range provided by the min/max options');
            $this->is($e->getMessage(), 'The date must be after 31/12/1805 10:00:00.', '->clean() check exception message');
        }
        try {
            $v->clean('18 october 2108');
            $this->fail('->clean() throws an exception if the date is not within the range provided by the min/max options');
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an exception if the date is not within the range provided by the min/max options');
            $this->is($e->getMessage(), 'The date must be before 31/12/2107 10:50:00.', '->clean() check exception message');
        }

        // timezones
        $defaultTimezone = new DateTimeZone(date_default_timezone_get());
        $otherTimezone = new DateTimeZone('US/Pacific');
        if ($defaultTimezone->getOffset(new DateTime()) == $otherTimezone->getOffset(new DateTime())) {
            $otherTimezone = new DateTimeZone('US/Eastern');
        }

        $date = new DateTime('2000-01-01T00:00:00-00:00');
        $date->setTimezone($otherTimezone);
        $v->setOption('min', null);
        $v->setOption('max', null);
        $v->setOption('with_time', true);
        $clean = $v->clean($date->format(DATE_ATOM));

        // did it convert from the other timezone to the default timezone?
        $date->setTimezone($defaultTimezone);
        $this->is($clean, $date->format('Y-m-d H:i:s'), '->clean() respects incoming and default timezones');
    }
}
