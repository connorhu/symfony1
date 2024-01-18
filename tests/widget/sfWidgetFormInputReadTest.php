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
class sfWidgetFormInputReadTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $w = new sfWidgetFormInputRead();

        // ->render()
        $this->diag('->render()');
        $this->is($w->render('foo'), '<input type="hidden" name="foo" id="foo" /><input type="text" readonly="readonly" style="border: 0;" />', '->render() renders the widget as HTML');
        $this->is($w->render('foo', 'bar'), '<input type="hidden" name="foo" value="bar" id="foo" /><input type="text" value="bar" readonly="readonly" style="border: 0;" />', '->render() can take a value for the input');
        $this->is($w->render('foo', '', array('class' => 'foobar', 'style' => 'width: 500px;')), '<input type="hidden" name="foo" value="" id="foo" /><input type="text" value="" readonly="readonly" style="border: 0; width: 500px;" class="foobar" />', '->render() can take HTML attributes as its third argument');

        $w = new sfWidgetFormInputRead(array('text' => 'Read text'));
        $this->is($w->render('foo', 'bar'), '<input type="hidden" name="foo" value="bar" id="foo" /><input type="text" value="Read text" readonly="readonly" style="border: 0;" />', '->render() can take a value for the input and another value for read input');

        $w = new sfWidgetFormInputRead(array(), array('class' => 'foobar', 'style' => 'width: 500px;'));
        $this->is($w->render('foo'), '<input type="hidden" name="foo" id="foo" /><input type="text" readonly="readonly" style="border: 0; width: 500px;" class="foobar" />', '__construct() can take default HTML attributes');
        $this->is($w->render('foo', null, array('class' => 'barfoo')), '<input type="hidden" name="foo" id="foo" /><input type="text" readonly="readonly" style="border: 0;" class="barfoo" />', '->render() can override default attributes');
    }
}
