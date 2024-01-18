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
class sfCacheSessionStorageTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $this->markTestSkipped('make this test work');
        return;

        $app = 'frontend';

        require_once __DIR__.'/../../test/bootstrap/functional.php';

        $_test_dir = realpath(__DIR__.'/../../test/');

        sfConfig::set('sf_symfony_lib_dir', realpath($_test_dir.'/../lib'));

        // initialize the storage
        try {
            $storage = new sfCacheSessionStorage();
            $this->fail('->__construct() does not throw an exception when not provided a cache option');
        } catch (InvalidArgumentException $e) {
            $this->pass('->__construct() throws an exception when not provided a cache option');
        }

        $storage = new sfCacheSessionStorage(array('cache' => array('class' => 'sfAPCCache', 'param' => array())));
        $this->ok($storage instanceof sfStorage, '->__construct() is an instance of sfStorage');

        $storage->write('test', 123);

        $this->is($storage->read('test'), 123, '->read() can read data that has been written to storage');

        // regenerate()
        $oldSessionData = 'foo:bar';
        $key = md5($oldSessionData);

        $storage->write($key, $oldSessionData);
        $session_id = session_id();
        $storage->regenerate(false);
        $this->is($storage->read($key), $oldSessionData, '->regenerate() regenerated the session with a different session id');
        $this->isnt(session_id(), $session_id, '->regenerate() regenerated the session with a different session id');

        $storage->regenerate(true);
        $this->isnt($storage->read($key), $oldSessionData, '->regenerate() regenerated the session with a different session id and destroyed data');
        $this->isnt(session_id(), $session_id, '->regenerate() regenerated the session with a different session id');

        $storage->remove($key);
        $this->is($storage->read($key), null, '->remove() removes data from the storage');

        // shutdown the storage
        $storage->shutdown();
    }
}
