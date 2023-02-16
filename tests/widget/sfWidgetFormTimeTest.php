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
class sfWidgetFormTimeTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $w = new sfWidgetFormTime(array('with_seconds' => true));

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->render()
        $this->diag('->render()');

        foreach (array(
            '12:30:35',
            mktime(12, 30, 35, 15, 10, 2005),
        ) as $date) {
            $dom->loadHTML($w->render('foo', $date));
            $css = new sfDomCssSelector($dom);

            // selected date
            $this->is($css->matchSingle('#foo_hour option[value="12"][selected="selected"]')->getValue(), '12', '->render() renders a select tag for the hour');
            $this->is($css->matchSingle('#foo_minute option[value="30"][selected="selected"]')->getValue(), '30', '->render() renders a select tag for the minute');
            $this->is($css->matchSingle('#foo_second option[value="35"][selected="selected"]')->getValue(), '35', '->render() renders a select tag for the second');
        }

        // time as an array
        $this->diag('time as an array');
        $dom->loadHTML($w->render('foo', array('hour' => 12, 'minute' => '30', 'second' => 35)));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo_hour option[value="12"][selected="selected"]')->getValue(), '12', '->render() renders a select tag for the hour');
        $this->is($css->matchSingle('#foo_minute option[value="30"][selected="selected"]')->getValue(), '30', '->render() renders a select tag for the minute');
        $this->is($css->matchSingle('#foo_second option[value="35"][selected="selected"]')->getValue(), '35', '->render() renders a select tag for the second');

        // time as an array - single digits
        $this->diag('time as an array - single digits');
        $dom->loadHTML($w->render('foo', '01:03:05'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo_hour option[selected="selected"]')->getValue(), '01', '->render() renders a select tag for the hour');
        $this->is($css->matchSingle('#foo_minute option[selected="selected"]')->getValue(), '03', '->render() renders a select tag for the minute');
        $this->is($css->matchSingle('#foo_second option[selected="selected"]')->getValue(), '05', '->render() renders a select tag for the second');

        // invalid time
        $this->diag('time as an array');
        $dom->loadHTML($w->render('foo', array('hour' => null, 'minute' => '30')));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo_hour option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the hour');
        $this->is($css->matchSingle('#foo_minute option[selected="selected"]')->getValue(), '30', '->render() renders a select tag for the minute');
        $this->is($css->matchSingle('#foo_second option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the second');

        $dom->loadHTML($w->render('foo', 'invalidtime'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo_hour option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the hour');
        $this->is($css->matchSingle('#foo_minute option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the minute');
        $this->is($css->matchSingle('#foo_second option[selected="selected"]')->getValue(), '', '->render() renders a select tag for the second');

        // number of options in each select
        $this->diag('number of options in each select');
        $dom->loadHTML($w->render('foo', '12:30:35'));
        $css = new sfDomCssSelector($dom);
        $this->is(count($css->matchAll('#foo_hour option')->getNodes()), 25, '->render() renders a select tag for the 24 hours in a day');
        $this->is(count($css->matchAll('#foo_minute option')->getNodes()), 61, '->render() renders a select tag for the 60 minutes in an hour');
        $this->is(count($css->matchAll('#foo_second option')->getNodes()), 61, '->render() renders a select tag for the 60 seconds in a minute');

        // can_be_empty option
        $this->diag('can_be_empty option');
        $w->setOption('can_be_empty', false);
        $dom->loadHTML($w->render('foo', '2005-10-15'));
        $css = new sfDomCssSelector($dom);
        $this->is(count($css->matchAll('#foo_hour option')->getNodes()), 24, '->render() renders a select tag for the 24 hours around in a day');
        $this->is(count($css->matchAll('#foo_minute option')->getNodes()), 60, '->render() renders a select tag for the 60 minutes in an hour');
        $this->is(count($css->matchAll('#foo_second option')->getNodes()), 60, '->render() renders a select tag for the 60 seconds in a minute');
        $w->setOption('can_be_empty', true);

        // empty_values
        $this->diag('empty_values option');
        $w->setOption('empty_values', array('hour' => 'HOUR', 'minute' => 'MINUTE', 'second' => 'SECOND'));
        $dom->loadHTML($w->render('foo', '2005-10-15'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('#foo_hour option')->getNode()->nodeValue, 'HOUR', '->configure() can change the empty values');
        $this->is($css->matchSingle('#foo_minute option')->getNode()->nodeValue, 'MINUTE', '->configure() can change the empty values');
        $this->is($css->matchSingle('#foo_second option')->getNode()->nodeValue, 'SECOND', '->configure() can change the empty values');
        $w->setOption('empty_values', array('hour' => '', 'minute' => '', 'second' => ''));

        // format option
        $this->diag('format option');
        $this->like($css->matchSingle('#foo_hour')->getNode()->nextSibling->nodeValue, '/^:/', '->render() renders 3 selects with a default : as a separator');
        $this->is($css->matchSingle('#foo_minute')->getNode()->nextSibling->nodeValue, ':', '->render() renders 3 selects with a default : as a separator');

        $w->setOption('format', '%hour%#%minute%#%second%');
        $dom->loadHTML($w->render('foo', '12:30:35'));
        $css = new sfDomCssSelector($dom);
        $this->like($css->matchSingle('#foo_hour')->getNode()->nextSibling->nodeValue, '/^#/', '__construct() can change the default format');
        $this->is($css->matchSingle('#foo_minute')->getNode()->nextSibling->nodeValue, '#', '__construct() can change the default format');

        $w->setOption('format', '%minute%#%hour%#%second%');
        $dom->loadHTML($w->render('foo', '12:30:35'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('select')->getNode()->getAttribute('name'), 'foo[minute]', '__construct() can change the default time format');

        // hours / minutes / seconds options
        $this->diag('hours / minutes / seconds options');
        $w->setOption('hours', array(1 => 1, 2 => 2, 3 => 3, 4 => 4));
        $w->setOption('minutes', array(1 => 1, 2 => 2));
        $w->setOption('seconds', array(15 => 15, 30 => 30, 45 => 45));
        $dom->loadHTML($w->render('foo', '12:30:35'));
        $css = new sfDomCssSelector($dom);
        $this->is(count($css->matchAll('#foo_hour option')->getNodes()), 5, '__construct() can change the default array used for hours');
        $this->is(count($css->matchAll('#foo_minute option')->getNodes()), 3, '__construct() can change the default array used for minutes');
        $this->is(count($css->matchAll('#foo_second option')->getNodes()), 4, '__construct() can change the default array used for seconds');

        // with_seconds option
        $this->diag('with_seconds option');
        $w->setOption('with_seconds', false);
        $dom->loadHTML($w->render('foo', '12:30:35'));
        $css = new sfDomCssSelector($dom);
        $this->is(count($css->matchAll('#foo_second option')->getNodes()), 0, '__construct() can enable or disable the seconds select box with the with_seconds option');

        $w->setOption('format_without_seconds', '%hour%#%minute%');
        $dom->loadHTML($w->render('foo', '12:30:35'));
        $css = new sfDomCssSelector($dom);
        $this->like($css->matchSingle('#foo_hour')->getNode()->nextSibling->nodeValue, '/^#/', '__construct() can change the default format');
        $this->ok(!count($css->matchSingle('#foo_second')->getNodes()), '__construct() can change the default format');

        // attributes
        $this->diag('attributes');
        $w->setOption('with_seconds', true);
        $dom->loadHTML($w->render('foo', '12:30:35', array('disabled' => 'disabled')));
        $this->is(count($css->matchAll('select[disabled="disabled"]')->getNodes()), 3, '->render() takes the attributes into account for all the three embedded widgets');

        $w->setAttribute('disabled', 'disabled');
        $dom->loadHTML($w->render('foo', '12:30:35'));
        $this->is(count($css->matchAll('select[disabled="disabled"]')->getNodes()), 3, '->render() takes the attributes into account for all the three embedded widgets');

        // id_format
        $this->diag('id_format');
        $w->setOption('with_seconds', true);
        $w->setIdFormat('id_%s');
        $dom->loadHTML($w->render('foo'));
        $this->is(count($css->matchAll('#id_foo_hour')), 1, '->render() uses id_format for hour');
        $this->is(count($css->matchAll('#id_foo_minute')), 1, '->render() uses id_format for minute');
        $this->is(count($css->matchAll('#id_foo_second')), 1, '->render() uses id_format for second');
    }
}
