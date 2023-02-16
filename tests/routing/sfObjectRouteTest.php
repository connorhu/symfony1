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
require_once __DIR__.'/../fixtures/myObjectRoute.php';
require_once __DIR__.'/../fixtures/Foo2.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfObjectRouteTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // simulate Doctrine route

        // ->generate()
        $this->diag('->generate()');
        $route = new myObjectRoute('/:id', array(), array(), array('model' => 'Foo', 'type' => 'object'));
        $this->is($route->generate(array('sf_subject' => new Foo2())), '/1', '->generate() generates a URL with the given parameters');
    }
}
