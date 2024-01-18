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
class sfSessionStorageTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $this->markTestSkipped('make this test work');
        return;

        $app = 'frontend';

        require_once __DIR__.'/../../test/bootstrap/functional.php';

        ob_start();

        $_test_dir = realpath(__DIR__.'/../../test');

        sfConfig::set('sf_symfony_lib_dir', realpath($_test_dir.'/../lib'));

        // initialize the storage
        try {
            $storage = new sfSessionStorage();
            $this->pass('->__construct() does not throw an exception when not provided with options');
            $storage->shutdown();
        } catch (InvalidArgumentException $e) {
            $this->fail('->__construct() Startup failure');
        }

        $storage = new sfSessionStorage();
        $this->ok($storage instanceof sfStorage, '->__construct() is an instance of sfStorage');

        $storage->write('test', 123);

        $this->is($storage->read('test'), 123, '->read() can read data that has been written to storage');

        // regenerate()
        $oldSessionData = 'foo:bar';
        $key = md5($oldSessionData);

        $storage->write($key, $oldSessionData);
        $session_id = session_id();
        $storage->regenerate(false);
        $this->is($storage->read($key), $oldSessionData, '->regenerate(false) regenerated the session with a different session id - this class by default doesn\'t regen the id');
        $this->isnt(session_id(), $session_id, '->regenerate(false) regenerated the session with a different session id');

        $storage->regenerate(true);
        $this->is($storage->read($key), $oldSessionData, '->regenerate(true) regenerated the session with a different session id and destroyed data');
        $this->isnt(session_id(), $session_id, '->regenerate(true) regenerated the session with a different session id');

        $storage->remove($key);
        $this->is($storage->read($key), null, '->remove() removes data from the storage');

        // shutdown the storage
        $storage->shutdown();
    }
}
