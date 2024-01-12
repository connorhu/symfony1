<?php

namespace Symfony1\Components\Config;

use Symfony1\Components\Util\ParameterHolder;
use Symfony1\Components\Util\Toolkit;
use LogicException;
use function is_array;
use function array_walk_recursive;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
* sfConfigHandler allows a developer to create a custom formatted configuration
file pertaining to any information they like and still have it auto-generate
PHP code.
*
* @author Fabien Potencier <fabien.potencier@symfony-project.com>
* @author Sean Kerr <sean@code-box.org>
*
* @version SVN: $Id$
*/
abstract class ConfigHandler
{
    /**
     * @var ParameterHolder
     */
    protected $parameterHolder;
    /**
     * Class constructor.
     *
     * @see initialize()
     *
     * @param (array | null) $parameters
     */
    public function __construct($parameters = null)
    {
        $this->initialize($parameters);
    }
    /**
     * Initializes this configuration handler.
     *
     * @param array $parameters An associative array of initialization parameters
     *
     * @throws <b>sfInitializationException</b> If an error occurs while initializing this ConfigHandler
     */
    public function initialize($parameters = null)
    {
        $this->parameterHolder = new ParameterHolder();
        $this->parameterHolder->add($parameters);
    }
    /**
     * Executes this configuration handler.
     *
     * @param array $configFiles An array of filesystem path to a configuration file
     *
     * @return string Data to be written to a cache file
     *
     * @throws <b>sfConfigurationException</b> If a requested configuration file does not exist or is not readable
     * @throws <b>sfParseException</b> If a requested configuration file is improperly formatted
     */
    public abstract function execute($configFiles);
    /**
     * Replaces constant identifiers in a value.
     *
     * If the value is an array replacements are made recursively.
     *
     * @param mixed $value The value on which to run the replacement procedure
     *
     * @return (array | mixed | string) The new value
     */
    public static function replaceConstants($value)
    {
        if (is_array($value)) {
            array_walk_recursive($value, function (&$value) {
                $value = Toolkit::replaceConstants($value);
            });
        } else {
            $value = Toolkit::replaceConstants($value);
        }
        return $value;
    }
    /**
     * Replaces a relative filesystem path with an absolute one.
     *
     * @param string $path A relative filesystem path
     *
     * @return string The new path
     */
    public static function replacePath($path)
    {
        if (is_array($path)) {
            array_walk_recursive($path, function (&$path) {
                $path = ConfigHandler::replacePath($path);
            });
        } else {
            if (!Toolkit::isPathAbsolute($path)) {
                // not an absolute path so we'll prepend to it
                $path = Config::get('sf_app_dir') . '/' . $path;
            }
        }
        return $path;
    }
    /**
     * Gets the parameter holder for this configuration handler.
     *
     * @return ParameterHolder A sfParameterHolder instance
     */
    public function getParameterHolder()
    {
        return $this->parameterHolder;
    }
    /**
     * Returns the configuration for the current config handler.
     *
     * @param array $configFiles An array of ordered configuration files
     *
     * @throws LogicException no matter what
     */
    public static function getConfiguration(array $configFiles)
    {
        throw new LogicException('You must call the ::getConfiguration() method on a concrete config handler class');
    }
}
class_alias(ConfigHandler::class, 'sfConfigHandler', false);