<?php

namespace Symfony1\Components\Cache;

use Exception;
use Symfony1\Components\Exception\Exception as ExceptionException;
use function unserialize;
use function is_callable;
use function ob_start;
use function ob_implicit_flush;
use function call_user_func_array;
use function ob_end_clean;
use function ob_get_clean;
use function serialize;
use function md5;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * This class can be used to cache the result and output of any PHP callable (function and method calls).
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class FunctionCache
{
    protected $cache;
    /**
     * Constructor.
     *
     * @param Cache $cache An sfCache object instance
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
    /**
    * Calls a cacheable function or method (or not if there is already a cache for it).
    *
    * Arguments of this method are read with func_get_args. So it doesn't appear in the function definition.
    *
    * The first argument can be any PHP callable:
    *
    * $cache->call('functionName', array($arg1, $arg2));
    $cache->call(array($object, 'methodName'), array($arg1, $arg2));
    *
    * @param mixed $callable A PHP callable
    * @param array $arguments An array of arguments to pass to the callable
    *
    * @return mixed The result of the function/method
    *
    * @throws Exception
    * @throws ExceptionException
    */
    public function call($callable, $arguments = array())
    {
        // Generate a cache id
        $key = $this->computeCacheKey($callable, $arguments);
        $serialized = $this->cache->get($key);
        if (null !== $serialized) {
            $data = unserialize($serialized);
        } else {
            $data = array();
            if (!is_callable($callable)) {
                throw new ExceptionException('The first argument to call() must be a valid callable.');
            }
            ob_start();
            ob_implicit_flush(false);
            try {
                $data['result'] = call_user_func_array($callable, $arguments);
            } catch (Exception $e) {
                ob_end_clean();
                throw $e;
            }
            $data['output'] = ob_get_clean();
            $this->cache->set($key, serialize($data));
        }
        echo $data['output'];
        return $data['result'];
    }
    /**
     * Returns the cache instance.
     *
     * @return Cache The sfCache instance
     */
    public function getCache()
    {
        return $this->cache;
    }
    /**
     * Computes the cache key for a given callable and the arguments.
     *
     * @param mixed $callable A PHP callable
     * @param array $arguments An array of arguments to pass to the callable
     *
     * @return string The associated cache key
     */
    public function computeCacheKey($callable, $arguments = array())
    {
        return md5(serialize($callable) . serialize($arguments));
    }
}
class_alias(FunctionCache::class, 'sfFunctionCache', false);