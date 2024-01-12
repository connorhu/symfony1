<?php

namespace Symfony1\Components\Storage;

use Symfony1\Components\Database\Database;
use PDO;
use Symfony1\Components\Exception\InitializationException;
use Propel;
use Symfony1\Components\Exception\DatabaseException;
use function array_merge;
use function session_set_save_handler;
use function session_start;
use function get_class;
use function session_id;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Base class for all sfStorage that uses a sfDatabase object as a storage.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Sean Kerr <sean@code-box.org>
 *
 * @version SVN: $Id$
 */
abstract class DatabaseSessionStorage extends SessionStorage
{
    /**
     * @var Database
     */
    protected $db;
    /**
     * @var PDO
     */
    protected $con;
    /**
    * Available options:.
    *
    * * db_table:    The database table in which session data will be stored
    database:    The sfDatabase object to use
    db_id_col:   The database column in which the session id will be stored (sess_id by default)
    db_data_col: The database column in which the session data will be stored (sess_data by default)
    db_time_col: The database column in which the session timestamp will be stored (sess_time by default)
    *
    * @param array $options An associative array of options
    *
    * @return (bool | void)
    *
    * @throws InitializationException
    *
    * @see sfSessionStorage
    */
    public function initialize($options = array())
    {
        $options = array_merge(array('db_id_col' => 'sess_id', 'db_data_col' => 'sess_data', 'db_time_col' => 'sess_time'), $options);
        // disable auto_start
        $options['auto_start'] = false;
        // initialize the parent
        parent::initialize($options);
        if (!isset($this->options['db_table'])) {
            throw new InitializationException('You must provide a "db_table" option to sfDatabaseSessionStorage.');
        }
        if (!isset($this->options['database'])) {
            throw new InitializationException('You must provide a "database" option to sfDatabaseSessionStorage.');
        }
        // use this object as the session handler
        session_set_save_handler(array($this, 'sessionOpen'), array($this, 'sessionClose'), array($this, 'sessionRead'), array($this, 'sessionWrite'), array($this, 'sessionDestroy'), array($this, 'sessionGC'));
        // start our session
        session_start();
    }
    /**
     * Closes a session.
     *
     * @return bool true, if the session was closed, otherwise false
     */
    public function sessionClose()
    {
        // do nothing
        return true;
    }
    /**
     * Opens a session.
     *
     * @param string $path (ignored)
     * @param string $name (ignored)
     *
     * @return bool true, if the session was opened, otherwise an exception is thrown
     *
     * @throws <b>DatabaseException</b> If a connection with the database does not exist or cannot be created
     */
    public function sessionOpen($path = null, $name = null)
    {
        // what database are we using?
        /**
         * @var Database $database
         */
        $database = $this->options['database'];
        // get the database and connection
        $databaseClass = get_class($database);
        if ('sfPropelDatabase' == $databaseClass) {
            $this->db = Propel::getConnection($database->getParameter('name'));
        } elseif ('sfDoctrineDatabase' == $databaseClass) {
            $this->db = $database->getConnection();
        } else {
            $this->db = $database->getResource();
        }
        $this->con = $database->getConnection();
        if (null === $this->db && null === $this->con) {
            throw new DatabaseException('Database connection does not exist. Unable to open session.');
        }
        return true;
    }
    /**
     * Destroys a session.
     *
     * @param string $id A session ID
     *
     * @return bool true, if the session was destroyed, otherwise an exception is thrown
     *
     * @throws <b>DatabaseException</b> If the session cannot be destroyed
     */
    public abstract function sessionDestroy($id);
    /**
     * Cleans up old sessions.
     *
     * @param int $lifetime The lifetime of a session
     *
     * @return bool true, if old sessions have been cleaned, otherwise an exception is thrown
     *
     * @throws <b>DatabaseException</b> If any old sessions cannot be cleaned
     */
    public abstract function sessionGC($lifetime);
    /**
     * Reads a session.
     *
     * @param string $id A session ID
     *
     * @return bool true, if the session was read, otherwise an exception is thrown
     *
     * @throws <b>DatabaseException</b> If the session cannot be read
     */
    public abstract function sessionRead($id);
    /**
     * Writes session data.
     *
     * @param string $id A session ID
     * @param string $data A serialized chunk of session data
     *
     * @return bool true, if the session was written, otherwise an exception is thrown
     *
     * @throws <b>DatabaseException</b> If the session data cannot be written
     */
    public abstract function sessionWrite($id, $data);
    /**
     * Regenerates id that represents this storage.
     *
     * @param bool $destroy Destroy session when regenerating?
     *
     * @return (bool | void) True if session regenerated, false if error
     */
    public function regenerate($destroy = false)
    {
        if (self::$sessionIdRegenerated) {
            return;
        }
        $currentId = session_id();
        parent::regenerate($destroy);
        $newId = session_id();
        $this->sessionRead($newId);
        return $this->sessionWrite($newId, $this->sessionRead($currentId));
    }
}
class_alias(DatabaseSessionStorage::class, 'sfDatabaseSessionStorage', false);