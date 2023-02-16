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
class sfMySQLiStorageTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        ob_start();

        // Configure your database with the settings below in order to run the test

        $mysqli_config = array(
            'host' => getenv('MYSQL_HOST') ?? 'localhost',
            'username' => getenv('MYSQL_USER') ?? 'root',
            'password' => getenv('MYSQL_PASSWORD') ?? '',
        );

        $database = new sfMySQLiDatabase($mysqli_config);
        $connection = $database->getResource();

        // Creates test database
        mysqli_query($connection, 'DROP DATABASE IF EXISTS sf_mysqli_storage_unit_test');
        mysqli_query($connection, 'CREATE DATABASE sf_mysqli_storage_unit_test') or $this->fail('Cannot create database sf_mysqli_storage_unit_test');
        mysqli_select_db($connection, 'sf_mysqli_storage_unit_test');
        mysqli_query($connection, "CREATE TABLE `session` (
          `sess_id` varchar(40) NOT NULL PRIMARY KEY,
          `sess_time` int(10) unsigned NOT NULL default '0',
          `sess_data` text collate utf8_unicode_ci
        ) ENGINE=MyISAM")
          or $this->fail('Can not create table session');

        ini_set('session.use_cookies', 0);
        $session_id = '1';

        $storage = new sfMySQLiSessionStorage(array(
            'db_table' => 'session',
            'session_id' => $session_id,
            'database' => $database
        ));

        $this->ok($storage instanceof sfStorage, 'sfMySQLSessionStorage is an instance of sfStorage');
        $this->ok($storage instanceof sfDatabaseSessionStorage, 'sfMySQLSessionStorage is an instance of sfDatabaseSessionStorage');

        // regenerate()
        $storage->regenerate(false);
        $this->isnt(session_id(), $session_id, 'regenerate() regenerated the session id');
        $session_id = session_id();

        // do some session operations
        $_SESSION['foo'] = 'bar';
        $_SESSION['bar'] = 'foo';
        unset($_SESSION['foo']);
        $session_data = session_encode();

        // end of session
        session_write_close();

        // check session data in the database
        $result = mysqli_query($connection, sprintf('SELECT sess_data FROM session WHERE sess_id = "%s"', $session_id));
        list($thisSessData) = mysqli_fetch_row($result);
        $this->is(mysqli_num_rows($result), 1, 'session is stored in the database');
        $this->is($thisSessData, $session_data, 'session variables are stored in the database');

        mysqli_free_result($result);
        unset($thisSessData, $result);

        // sessionRead()
        try {
            $retrieved_data = $storage->sessionRead($session_id);
            $this->pass('sessionRead() does not throw an exception');
        } catch (Exception $e) {
            $this->fail('sessionRead() does not throw an exception');
        }
        $this->is($retrieved_data, $session_data, 'sessionRead() reads session data');

        // sessionWrite()
        $_SESSION['baz'] = 'woo';
        $session_data = session_encode();
        try {
            $write = $storage->sessionWrite($session_id, $session_data);
            $this->pass('sessionWrite() does not throw an exception');
        } catch (Exception $e) {
            $this->fail('sessionWrite() does not throw an exception');
        }

        $this->ok($write, 'sessionWrite() returns true');
        $this->is($storage->sessionRead($session_id), $session_data, 'sessionWrite() wrote session data');

        // sessionDestroy()
        try {
            $storage->sessionDestroy($session_id);
            $this->pass('sessionDestroy() does not throw an exception');
        } catch (Exception $e) {
            $this->fail('sessionDestroy() does not throw an exception');
        }

        $result = mysqli_query($connection, sprintf('SELECT COUNT(sess_id) FROM session WHERE sess_id = "%s"', $session_id));

        list($count) = mysqli_fetch_row($result);
        $this->is($count, '0', 'session is removed from the database');

        mysqli_free_result($result);
        unset($count, $result);

        mysqli_query($connection, 'DROP DATABASE sf_mysqli_storage_unit_test');

        // shutdown the storage
        $storage->shutdown();

        // shutdown the database
        $database->shutdown();

        unset($mysqli_config);

        ob_end_clean();
    }
}
