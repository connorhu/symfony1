<?php

namespace Symfony1\Components\Cache;

use Symfony1\Components\Exception\InitializationException;
use function function_exists;
use function ini_get;
use function eaccelerator_get;
use function eaccelerator_put;
use function eaccelerator_rm;
use function eaccelerator_list_keys;
use function is_array;
use function preg_match;
use function eaccelerator_gc;
use function strpos;
use function substr;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Cache class that stores cached content in EAccelerator.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 *
 * @deprecated
 */
class EAcceleratorCache extends Cache
{
    /**
     * Initializes this sfCache instance.
     *
     * Available options:
     *
     * * see sfCache for options available for all drivers
     *
     * @see sfCache
     *
     * @param array $options
     *
     * @throws InitializationException
     */
    public function initialize($options = array())
    {
        parent::initialize($options);
        if (!function_exists('eaccelerator_put') || !ini_get('eaccelerator.enable')) {
            throw new InitializationException('You must have EAccelerator installed and enabled to use sfEAcceleratorCache class (or perhaps you forgot to add --with-eaccelerator-shared-memory when installing).');
        }
    }
    /**
     * @see sfCache
     *
     * @param string $key
     * @param (mixed | null) $default
     *
     * @return (string | null)
     */
    public function get($key, $default = null)
    {
        $value = eaccelerator_get($this->getOption('prefix') . $key);
        return null === $value ? $default : $value;
    }
    /**
     * @see sfCache
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return null !== eaccelerator_get($this->getOption('prefix' . $key));
    }
    /**
     * @see sfCache
     *
     * @param string $key
     * @param string $data
     * @param (int | null) $lifetime
     *
     * @return bool
     */
    public function set($key, $data, $lifetime = null)
    {
        return eaccelerator_put($this->getOption('prefix') . $key, $data, $this->getLifetime($lifetime));
    }
    /**
     * @see sfCache
     */
    public function remove($key)
    {
        return eaccelerator_rm($this->getOption('prefix') . $key);
    }
    /**
     * @see sfCache
     */
    public function removePattern($pattern)
    {
        $infos = eaccelerator_list_keys();
        if (is_array($infos)) {
            $regexp = self::patternToRegexp($this->getOption('prefix') . $pattern);
            foreach ($infos as $info) {
                if (preg_match($regexp, $info['name'])) {
                    eaccelerator_rm($this->getOption('prefix') . $key);
                }
            }
        }
    }
    /**
     * @see sfCache
     */
    public function clean($mode = Cache::ALL)
    {
        if (Cache::OLD === $mode) {
            return eaccelerator_gc();
        }
        $infos = eaccelerator_list_keys();
        if (is_array($infos)) {
            foreach ($infos as $info) {
                if (false !== strpos($info['name'], $this->getOption('prefix'))) {
                    // eaccelerator bug (http://eaccelerator.net/ticket/287)
                    $key = 0 === strpos($info['name'], ':') ? substr($info['name'], 1) : $info['name'];
                    if (!eaccelerator_rm($key)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    /**
     * @see sfCache
     */
    public function getLastModified($key)
    {
        if ($info = $this->getCacheInfo($key)) {
            return $info['created'];
        }
        return 0;
    }
    /**
     * @see sfCache
     */
    public function getTimeout($key)
    {
        if ($info = $this->getCacheInfo($key)) {
            return -1 == $info['ttl'] ? 0 : $info['created'] + $info['ttl'];
        }
        return 0;
    }
    protected function getCacheInfo($key)
    {
        $infos = eaccelerator_list_keys();
        if (is_array($infos)) {
            foreach ($infos as $info) {
                if ($this->getOption('prefix') . $key == $info['name']) {
                    return $info;
                }
            }
        }
        return null;
    }
}
class_alias(EAcceleratorCache::class, 'sfEAcceleratorCache', false);