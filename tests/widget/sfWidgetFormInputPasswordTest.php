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
class sfWidgetFormInputPasswordTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $w = new sfWidgetFormInputPassword();

        // ->render()
        $this->diag('->render()');
        $this->is($w->render('foo'), '<input type="password" name="foo" id="foo" />', '->render() renders the widget as HTML');

        $this->is($w->render('foo', 'bar'), '<input type="password" name="foo" id="foo" />', '->render() renders the widget as HTML');

        $w->setOption('always_render_empty', false);
        $this->is($w->render('foo', 'bar'), '<input type="password" name="foo" value="bar" id="foo" />', '->render() renders the widget as HTML');
    }
}
