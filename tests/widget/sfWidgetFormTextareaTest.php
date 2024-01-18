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
class sfWidgetFormTextareaTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $w = new sfWidgetFormTextarea();

        // ->render()
        $this->diag('->render()');
        $this->is($w->render('foo', 'bar'), '<textarea rows="4" cols="30" name="foo" id="foo">bar</textarea>', '->render() renders the widget as HTML');
        $this->is($w->render('foo', '<bar>'), '<textarea rows="4" cols="30" name="foo" id="foo">&lt;bar&gt;</textarea>', '->render() escapes the content');
        $this->is($w->render('foo', '&lt;bar&gt;'), '<textarea rows="4" cols="30" name="foo" id="foo">&lt;bar&gt;</textarea>', '->render() does not double escape content');

        // change default attributes
        $this->diag('change default attributes');
        $w->setAttribute('rows', 10);
        $this->is($w->render('foo', 'bar'), '<textarea rows="10" cols="30" name="foo" id="foo">bar</textarea>', '->render() renders the widget as HTML');
    }
}
