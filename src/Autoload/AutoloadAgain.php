<?php

namespace Symfony1\Components\Autoload;

use function spl_autoload_functions;
use function version_compare;
use function is_array;
use function array_search;
use function spl_autoload_call;
use function class_exists;
use function interface_exists;
use function spl_autoload_register;
use function spl_autoload_unregister;
use const PHP_VERSION;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Autoload again for dev environments.
 *
 * @author Kris Wallsmith <kris.wallsmith@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class AutoloadAgain
{
    protected static $instance;
    protected $registered = false;
    protected $reloaded = false;
    /**
     * Constructor.
     */
    protected function __construct()
    {
    }
    /**
     * Returns the singleton autoloader.
     *
     * @return AutoloadAgain
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * Reloads the autoloader.
     *
     * @param string $class
     *
     * @return bool
     */
    public function autoload($class)
    {
        // only reload once
        if ($this->reloaded) {
            return false;
        }
        $autoloads = spl_autoload_functions();
        // as of PHP 5.2.11, spl_autoload_functions() returns the object as the first element of the array instead of the class name
        if (version_compare(PHP_VERSION, '5.2.11', '>=')) {
            foreach ($autoloads as $position => $autoload) {
                if (is_array($autoload) && $this === $autoload[0]) {
                    break;
                }
            }
        } else {
            $position = array_search(array(__CLASS__, 'autoload'), $autoloads, true);
        }
        if (isset($autoloads[$position + 1])) {
            $this->unregister();
            $this->register();
            // since we're rearranged things, call the chain again
            spl_autoload_call($class);
            return class_exists($class, false) || interface_exists($class, false);
        }
        $autoload = Autoload::getInstance();
        $autoload->reloadClasses(true);
        $this->reloaded = true;
        return $autoload->autoload($class);
    }
    /**
     * Returns true if the autoloader is registered.
     *
     * @return bool
     */
    public function isRegistered()
    {
        return $this->registered;
    }
    /**
     * Registers the autoloader function.
     */
    public function register()
    {
        if (!$this->isRegistered()) {
            spl_autoload_register(array($this, 'autoload'));
            $this->registered = true;
        }
    }
    /**
     * Unregisters the autoloader function.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'autoload'));
        $this->registered = false;
    }
}
class_alias(AutoloadAgain::class, 'sfAutoloadAgain', false);