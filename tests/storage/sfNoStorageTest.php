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
class sfNoStorageTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // initialize the storage
        $storage = new sfNoStorage();

        $this->ok($storage instanceof sfStorage, 'sfNoStorage is an instance of sfStorage');

        // shutdown the storage
        $storage->shutdown();
    }
}
