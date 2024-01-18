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
require_once __DIR__.'/../fixtures/myStorage.php';
require_once __DIR__.'/../fixtures/fakeStorage.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfStorageTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $this->markTestSkipped('create tests');
        // TODO tests?
    }
}
