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
class sfWidgetFormInputFileTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $w = new sfWidgetFormInputFile();

        // ->render()
        $this->diag('->render()');
        $this->is($w->render('foo'), '<input type="file" name="foo" id="foo" />', '->render() renders the widget as HTML');
    }
}
