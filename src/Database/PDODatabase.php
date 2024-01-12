<?php

namespace Symfony1\Components\Database;

use Symfony1\Components\Exception\DatabaseException;
use PDO;
use PDOException;
use Symfony1\Components\Config\Config;
use function call_user_func_array;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfPDODatabase provides connectivity for the PDO database abstraction layer.
 *
 * @author Daniel Swarbrick (daniel@pressure.net.nz)
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Sean Kerr <sean@code-box.org>
 * @author Dustin Whittle <dustin.whittle@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PDODatabase extends Database
{
    /**
     * Magic method for calling PDO directly via sfPDODatabase.
     *
     * @param string $method
     * @param array $arguments
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->getConnection(), $method), $arguments);
    }
    /**
     * Connects to the database.
     *
     * @throws <b>sfDatabaseException</b> If a connection could not be created
     */
    public function connect()
    {
        if (!($dsn = $this->getParameter('dsn'))) {
            // missing required dsn parameter
            throw new DatabaseException('Database configuration is missing the "dsn" parameter.');
        }
        try {
            $pdo_class = $this->getParameter('class', 'PDO');
            $username = $this->getParameter('username');
            $password = $this->getParameter('password');
            $persistent = $this->getParameter('persistent');
            $options = $persistent ? array(PDO::ATTR_PERSISTENT => true) : array();
            $this->connection = new $pdo_class($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }
        // lets generate exceptions instead of silent failures
        if (Config::get('sf_debug')) {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } else {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        }
        // compatability
        $compatability = $this->getParameter('compat');
        if ($compatability) {
            $this->connection->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
        }
        // nulls
        $nulls = $this->getParameter('nulls');
        if ($nulls) {
            $this->connection->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
        }
        // auto commit
        $autocommit = $this->getParameter('autocommit');
        if ($autocommit) {
            $this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
        }
        $this->resource = $this->connection;
    }
    /**
     * Execute the shutdown procedure.
     */
    public function shutdown()
    {
        if (null !== $this->connection) {
            @($this->connection = null);
        }
    }
}
class_alias(PDODatabase::class, 'sfPDODatabase', false);