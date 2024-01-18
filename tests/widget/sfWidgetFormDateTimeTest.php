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
class sfWidgetFormDateTimeTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $year = date('Y');

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        $w = new sfWidgetFormDateTime(array('with_time' => true, 'time' => array('with_seconds' => true)));

        // ->render()
        $this->diag('->render()');

        foreach (array(
            $year.'-10-15 12:30:35' => array('year' => $year, 'month' => '10', 'day' => '15', 'hour' => '12', 'minute' => '30', 'second' => '35'),
            time() => array('year' => date('Y'), 'month' => date('m'), 'day' => date('d'), 'hour' => date('H'), 'minute' => date('i'), 'second' => date('s')),
            'tomorrow 12:30:35' => array('year' => date('Y', time() + 86400), 'month' => date('m', time() + 86400), 'day' => date('d', time() + 86400), 'hour' => '12', 'minute' => '30', 'second' => '35'),
        ) as $date => $values) {
            $dom->loadHTML($w->render('foo', $date));
            $css = new sfDomCssSelector($dom);

            // selected date / time
            $this->is($css->matchSingle('#foo_year option[value="'.$values['year'].'"][selected="selected"]')->getValue(), $values['year'], '->render() renders a select tag for the year');
            $this->is($css->matchSingle('#foo_month option[value="'.$values['month'].'"][selected="selected"]')->getValue(), $values['month'], '->render() renders a select tag for the month');
            $this->is($css->matchSingle('#foo_day option[value="'.$values['day'].'"][selected="selected"]')->getValue(), $values['day'], '->render() renders a select tag for the day');
            $this->is($css->matchSingle('#foo_hour option[value="'.$values['hour'].'"][selected="selected"]')->getValue(), $values['hour'], '->render() renders a select tag for the hour');
            $this->is($css->matchSingle('#foo_minute option[value="'.$values['minute'].'"][selected="selected"]')->getValue(), $values['minute'], '->render() renders a select tag for the minute');
            $this->is($css->matchSingle('#foo_second option[value="'.$values['second'].'"][selected="selected"]')->getValue(), $values['second'], '->render() renders a select tag for the second');
        }

        // selected date / time
        $this->diag('selected date / time');
        $values = array('year' => $year, 'month' => '10', 'day' => '15', 'hour' => '12', 'minute' => '30', 'second' => '35');
        $dom->loadHTML($w->render('foo', $values));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo_year option[value="'.$values['year'].'"][selected="selected"]')->getValue(), $values['year'], '->render() renders a select tag for the year');
        $this->is($css->matchSingle('#foo_month option[value="'.$values['month'].'"][selected="selected"]')->getValue(), $values['month'], '->render() renders a select tag for the month');
        $this->is($css->matchSingle('#foo_day option[value="'.$values['day'].'"][selected="selected"]')->getValue(), $values['day'], '->render() renders a select tag for the day');
        $this->is($css->matchSingle('#foo_hour option[value="'.$values['hour'].'"][selected="selected"]')->getValue(), $values['hour'], '->render() renders a select tag for the hour');
        $this->is($css->matchSingle('#foo_minute option[value="'.$values['minute'].'"][selected="selected"]')->getValue(), $values['minute'], '->render() renders a select tag for the minute');
        $this->is($css->matchSingle('#foo_second option[value="'.$values['second'].'"][selected="selected"]')->getValue(), $values['second'], '->render() renders a select tag for the second');

        // invalid date / time
        $this->diag('invalid date / time');
        $values = array('year' => null, 'month' => '10', 'hour' => null, 'minute' => '30');
        $dom->loadHTML($w->render('foo', $values));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo_year option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the year');
        $this->is($css->matchSingle('#foo_month option[value="'.$values['month'].'"][selected="selected"]')->getValue(), $values['month'], '->render() renders a select tag for the month');
        $this->is($css->matchSingle('#foo_day option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the day');
        $this->is($css->matchSingle('#foo_hour option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the hour');
        $this->is($css->matchSingle('#foo_minute option[value="'.$values['minute'].'"][selected="selected"]')->getValue(), $values['minute'], '->render() renders a select tag for the minute');
        $this->is($css->matchSingle('#foo_second option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the second');

        $dom->loadHTML($w->render('foo', 'invaliddatetime'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo_year option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the year');
        $this->is($css->matchSingle('#foo_month option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the month');
        $this->is($css->matchSingle('#foo_day option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the day');
        $this->is($css->matchSingle('#foo_hour option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the hour');
        $this->is($css->matchSingle('#foo_minute option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the minute');
        $this->is($css->matchSingle('#foo_second option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the second');

        // number of options in each select
        $this->diag('number of options in each select');
        $dom->loadHTML($w->render('foo', $year.'-10-15 12:30:35'));
        $css = new sfDomCssSelector($dom);
        $this->is(count($css->matchAll('#foo_year option')->getNodes()), 12, '->render() renders a select tag for the 10 years around the current one');
        $this->is(count($css->matchAll('#foo_month option')->getNodes()), 13, '->render() renders a select tag for the 12 months in a year');
        $this->is(count($css->matchAll('#foo_day option')->getNodes()), 32, '->render() renders a select tag for the 31 days in a month');
        $this->is(count($css->matchAll('#foo_hour option')->getNodes()), 25, '->render() renders a select tag for the 24 hours in a day');
        $this->is(count($css->matchAll('#foo_minute option')->getNodes()), 61, '->render() renders a select tag for the 60 minutes in an hour');
        $this->is(count($css->matchAll('#foo_second option')->getNodes()), 61, '->render() renders a select tag for the 60 seconds in a minute');

        // date and time format option
        $this->diag('date and time format option');
        $this->is($css->matchSingle('#foo_day')->getNode()->nextSibling->nodeValue, '/', '->render() renders 3 selects with a default / as a format');
        $this->like($css->matchSingle('#foo_month')->getNode()->nextSibling->nodeValue, '#^/#', '->render() renders 3 selects with a default / as a format');
        $this->is($css->matchSingle('#foo_hour')->getNode()->nextSibling->nodeValue, ':', '->render() renders 3 selects with a default : as a format');
        $this->is($css->matchSingle('#foo_minute')->getNode()->nextSibling->nodeValue, ':', '->render() renders 3 selects with a default : as a format');

        $this->diag('change date and time format option');
        $w->setOption('date', array('format' => '%month%-%day%-%year%'));
        $w->setOption('time', array('format' => '%hour%!%minute%!%second%', 'with_seconds' => true));
        $dom->loadHTML($w->render('foo', $year.'-10-15 12:30:35'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo_day')->getNode()->nextSibling->nodeValue, '-', '__construct() can change the default format');
        $this->like($css->matchSingle('#foo_month')->getNode()->nextSibling->nodeValue, '/^-/', '__construct() can change the default format');
        $this->is($css->matchSingle('#foo_hour')->getNode()->nextSibling->nodeValue, '!', '__construct() can change the default format');
        $this->is($css->matchSingle('#foo_minute')->getNode()->nextSibling->nodeValue, '!', '__construct() can change the default format');

        // with_time option
        $this->diag('with_time option');

        $w = new sfWidgetFormDateTime(array('with_time' => false));
        $dom->loadHTML($w->render('foo', $year.'-10-15 12:30:35'));
        $css = new sfDomCssSelector($dom);
        $this->is(count($css->matchAll('#foo_hour')->getNodes()), 0, '->render() does not render the time if the with_time option is disabled');

        // date and time options as array
        $this->diag('date and time options as array');
        $w = new sfWidgetFormDateTime(array('date' => 'a string'));
        try {
            $w->render('foo');
            $this->fail('__construct() throws a InvalidArgumentException if the date/time options is not an array');
        } catch (InvalidArgumentException $e) {
            $this->pass('__construct() throws a InvalidArgumentException if the date/time options is not an array');
        }

        // attributes
        $this->diag('attributes');
        $w = new sfWidgetFormDateTime();
        $dom->loadHTML($w->render('foo', $year.'-10-15 12:30:35', array('date' => array('disabled' => 'disabled'), 'time' => array('disabled' => 'disabled'))));
        $this->is(count($css->matchAll('select[disabled="disabled"]')->getNodes()), 5, '->render() takes the attributes into account for all the five embedded widgets');

        $w->setAttribute('date', array('disabled' => 'disabled'));
        $w->setAttribute('time', array('disabled' => 'disabled'));
        $dom->loadHTML($w->render('foo', $year.'-10-15 12:30:35'));
        $this->is(count($css->matchAll('select[disabled="disabled"]')->getNodes()), 5, '->render() takes the attributes into account for all the five embedded widgets');

        // id_format
        $this->diag('id_format');
        $w = new sfWidgetFormDateTime();
        $w->setIdFormat('id_%s');
        $dom->loadHTML($w->render('foo'));
        $this->is(count($css->matchAll('#id_foo_month')), 1, '->render() month considers id_format');
        $this->is(count($css->matchAll('#id_foo_day')), 1, '->render() day considers id_format');
        $this->is(count($css->matchAll('#id_foo_year')), 1, '->render() year considers id_format');
        $this->is(count($css->matchAll('#id_foo_hour')), 1, '->render() hour considers id_format');
        $this->is(count($css->matchAll('#id_foo_minute')), 1, '->render() minute considers id_format');

        $w->setOption('date', array('id_format' => 'override_%s'));
        $w->setOption('time', array('id_format' => 'override_%s'));
        $dom->loadHTML($w->render('foo'));
        $this->is(count($css->matchAll('#override_foo_month')), 1, '->render() month does not override subwidget id_format');
        $this->is(count($css->matchAll('#override_foo_day')), 1, '->render() day does not override subwidget id_format');
        $this->is(count($css->matchAll('#override_foo_year')), 1, '->render() year does not override subwidget id_format');
        $this->is(count($css->matchAll('#override_foo_hour')), 1, '->render() hour does not override subwidget id_format');
        $this->is(count($css->matchAll('#override_foo_minute')), 1, '->render() minute does not override subwidget id_format');
    }
}
