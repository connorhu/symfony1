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
class sfWidgetFormInputTextTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $w = new sfWidgetFormInputText();

        // ->render()
        $this->diag('->render()');
        $this->is($w->render('foo'), '<input type="text" name="foo" id="foo" />', '->render() renders the widget as HTML');
        $this->is($w->render('foo', 'bar'), '<input type="text" name="foo" value="bar" id="foo" />', '->render() can take a value for the input');
        $this->is($w->render('foo', '', array('type' => 'password', 'class' => 'foobar')), '<input type="password" name="foo" value="" class="foobar" id="foo" />', '->render() can take HTML attributes as its third argument');

        $w = new sfWidgetFormInputText(array(), array('class' => 'foobar'));
        $this->is($w->render('foo'), '<input class="foobar" type="text" name="foo" id="foo" />', '__construct() can take default HTML attributes');
        $this->is($w->render('foo', null, array('class' => 'barfoo')), '<input class="barfoo" type="text" name="foo" id="foo" />', '->render() can override default attributes');
    }
}
