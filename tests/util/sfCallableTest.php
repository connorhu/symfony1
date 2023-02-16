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
require_once __DIR__.'/../fixtures/TrimTest.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfCallableTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // call()
        $this->diag('call()');
        $c = new sfCallable('trim');
        $this->is($c->call('  foo  '), 'foo', '->call() calls the callable with the given arguments');

        $c = new sfCallable(array('TrimTest', 'trimStatic'));
        $this->is($c->call('  foo  '), 'foo', '->call() calls the callable with the given arguments');

        $c = new sfCallable(array(new TrimTest(), 'trim'));
        $this->is($c->call('  foo  '), 'foo', '->call() calls the callable with the given arguments');

        $c = new sfCallable('nonexistantcallable');
        try {
            $c->call();
            $this->fail('->call() throws an sfException if the callable is not valid');
        } catch (sfException $e) {
            $this->pass('->call() throws an sfException if the callable is not valid');
        }

        // ->getCallable()
        $this->diag('->getCallable()');
        $c = new sfCallable('trim');
        $this->is($c->getCallable(), 'trim', '->getCallable() returns the current callable');
    }
}
