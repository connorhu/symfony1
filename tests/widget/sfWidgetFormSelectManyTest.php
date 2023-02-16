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
class sfWidgetFormSelectManyTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->render()
        $this->diag('->render()');
        $w = new sfWidgetFormSelectMany(array('choices' => array('foo' => 'bar', 'foobar' => 'foo')));
        $this->is($w->getOption('multiple'), true, '__construct() creates a multiple select tag');
    }
}
