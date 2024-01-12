<?php

namespace Symfony1\Components\Storage;

use function register_shutdown_function;
use function array_merge;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfStorage allows you to customize the way symfony stores its persistent data.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Sean Kerr <sean@code-box.org>
 *
 * @version SVN: $Id$
 */
abstract class Storage
{
    protected $options = array();
    /**
     * Class constructor.
     *
     * @see initialize()
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->initialize($options);
        if ($this->options['auto_shutdown']) {
            register_shutdown_function(array($this, 'shutdown'));
        }
    }
    /**
     * Initializes this Storage instance.
     *
     * Available options:
     *
     * * auto_shutdown: Whether to automatically save the changes to the session (true by default)
     *
     * @param array $options An associative array of options
     *
     * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfStorage
     */
    public function initialize($options = array())
    {
        $this->options = array_merge(array('auto_shutdown' => true), $options);
    }
    /**
     * Returns the option array.
     *
     * @return array The array of options
     */
    public function getOptions()
    {
        return $this->options;
    }
    /**
     * Reads data from this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string $key A unique key identifying your data
     *
     * @return mixed Data associated with the key
     *
     * @throws <b>sfStorageException</b> If an error occurs while reading data from this storage
     */
    public abstract function read($key);
    /**
     * Regenerates id that represents this storage.
     *
     * @param bool $destroy Destroy session when regenerating?
     *
     * @return bool True if session regenerated, false if error
     *
     * @throws <b>sfStorageException</b> If an error occurs while regenerating this storage
     */
    public abstract function regenerate($destroy = false);
    /**
     * Removes data from this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string $key A unique key identifying your data
     *
     * @return mixed Data associated with the key
     *
     * @throws <b>sfStorageException</b> If an error occurs while removing data from this storage
     */
    public abstract function remove($key);
    /**
     * Executes the shutdown procedure.
     *
     * @throws <b>sfStorageException</b> If an error occurs while shutting down this storage
     */
    public abstract function shutdown();
    /**
     * Writes data to this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string $key A unique key identifying your data
     * @param mixed $data Data associated with your key
     *
     * @throws <b>sfStorageException</b> If an error occurs while writing to this storage
     */
    public abstract function write($key, $data);
}
class_alias(Storage::class, 'sfStorage', false);