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
require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../fixtures/myViewCacheManager.php';
require_once __DIR__.'/../fixtures/myController4.php';
require_once __DIR__.'/../fixtures/myRequest6.php';
require_once __DIR__.'/../fixtures/myCache2.php';
require_once __DIR__.'/../fixtures/myRouting.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfViewCacheManagerTest extends Symfony1ProjectTestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        function get_cache_manager($context)
        {
            myCache2::clear();

            return new myViewCacheManager($context, new myCache2());
        }

        function get_cache_config($contextual = false)
        {
            return array(
                'withLayout' => false,
                'lifeTime' => 86400,
                'clientLifeTime' => 86400,
                'contextual' => $contextual,
                'vary' => array(),
            );
        }

        $this->resetSfConfig();

        $context = sfContext::getInstance(array('controller' => 'myController4', 'routing' => 'myRouting', 'request' => 'myRequest6'), true);

        $r = $context->routing;
        $r->connect('default', new sfRoute('/:module/:action/*'));

        // ->initialize()
        $this->diag('->initialize()');
        $m = new myViewCacheManager($context, $cache = new myCache2());
        $this->is($m->getCache(), $cache, '->initialize() takes a sfCache object as its second argument');

        // ->generateCacheKey()
        $this->diag('->generateCacheKey');
        $this->is($m->generateCacheKey('mymodule/myaction'), '/localhost/all/mymodule/myaction', '->generateCacheKey() creates a simple cache key from an internal URI');
        $this->is($m->generateCacheKey('mymodule/myaction', 'foo'), '/foo/all/mymodule/myaction', '->generateCacheKey() can take a hostName as second parameter');
        $this->is($m->generateCacheKey('mymodule/myaction', null, 'bar'), '/localhost/bar/mymodule/myaction', '->generateCacheKey() can take a serialized set of vary headers as third parameter');

        $this->is($m->generateCacheKey('mymodule/myaction?key1=value1&key2=value2'), '/localhost/all/mymodule/myaction/key1/value1/key2/value2', '->generateCacheKey() includes request parameters as key/value pairs');
        $this->is($m->generateCacheKey('mymodule/myaction?akey=value1&ckey=value2&bkey=value3'), '/localhost/all/mymodule/myaction/akey/value1/bkey/value3/ckey/value2', '->generateCacheKey() reorders request parameters alphabetically');

        try {
            $m->generateCacheKey('@rule?key=value');
            $this->fail('->generateCacheKey() throws an sfException when passed an internal URI with a rule');
        } catch (sfException $e) {
            $this->pass('->generateCacheKey() throws an sfException when passed an internal URI with a rule');
        }
        try {
            $m->generateCacheKey('@sf_cache_partial?module=mymodule&action=myaction');
            $this->pass('->generateCacheKey() does not throw an sfException when passed an internal URI with a @sf_cache_partial rule');
        } catch (sfException $e) {
            $this->fail('->generateCacheKey() does not throw an sfException when passed an internal URI with a @sf_cache_partial rule');
        }
        try {
            $m->generateCacheKey('@sf_cache_partial?key=value');
            $this->fail('->generateCacheKey() throws an sfException when passed an internal URI with a @sf_cache_partial rule with no module or action param');
        } catch (sfException $e) {
            $this->pass('->generateCacheKey() throws an sfException when passed an internal URI with a @sf_cache_partial rule with no module or action param');
        }

        $this->is($m->generateCacheKey('@sf_cache_partial?module=foo&action=bar&sf_cache_key=value'), '/localhost/all/sf_cache_partial/foo/bar/sf_cache_key/value', '->generateCacheKey() can deal with internal URIs to partials');

        $m = get_cache_manager($context);
        $m->addCache('foo', 'bar', get_cache_config(true));
        $this->is($m->generateCacheKey('@sf_cache_partial?module=foo&action=bar&sf_cache_key=value'), '/localhost/all/currentModule/currentAction/currentKey/currentValue/foo/bar/value', '->generateCacheKey() can deal with internal URIs to contextual partials');

        $this->is($m->generateCacheKey('@sf_cache_partial?module=foo&action=bar&sf_cache_key=value', null, null, 'baz'), '/localhost/all/baz/foo/bar/value', '->generateCacheKey() can take a prefix for contextual partials as fourth parameter');

        $m = get_cache_manager($context);
        $m->addCache('module', 'action', array('vary' => array('myheader', 'secondheader')));
        $this->is($m->generateCacheKey('module/action'), '/localhost/myheader-_foo_bar_-secondheader-_foo_bar_/module/action', '->generateCacheKey() creates a directory friendly vary cache key');

        // ->generateNamespace()
        $this->diag('->generateNamespace()');
        $m = get_cache_manager($context);

        // ->addCache()
        $this->diag('->addCache()');
        $m = get_cache_manager($context);
        $m->set('test', 'module/action');
        $this->is($m->has('module/action'), false, '->addCache() register a cache configuration for an action');

        $m->addCache('module', 'action', get_cache_config());
        $m->set('test', 'module/action');
        $this->is($m->get('module/action'), 'test', '->addCache() register a cache configuration for an action');

        // ->set()
        $this->diag('->set()');
        $m = get_cache_manager($context);
        $this->is($m->set('test', 'module/action'), false, '->set() returns false if the action is not cacheable');
        $m->addCache('module', 'action', get_cache_config());
        $this->is($m->set('test', 'module/action'), true, '->set() returns true if the action is cacheable');

        $m = get_cache_manager($context);
        $m->addCache('module', 'action', get_cache_config());
        $m->set('test', 'module/action');
        $this->is($m->get('module/action'), 'test', '->set() stores the first parameter in a key computed from the second parameter');

        $m = get_cache_manager($context);
        $m->addCache('module', 'action', get_cache_config());
        $m->set('test', 'module/action?key1=value1');
        $this->is($m->get('module/action?key1=value1'), 'test', '->set() works with URIs with parameters');
        $this->is($m->get('module/action?key2=value2'), null, '->set() stores a different version for each set of parameters');
        $this->is($m->get('module/action'), null, '->set() stores a different version for each set of parameters');

        $m = get_cache_manager($context);
        $m->addCache('module', 'action', get_cache_config());
        $m->set('test', '@sf_cache_partial?module=module&action=action');
        $this->is($m->get('@sf_cache_partial?module=module&action=action'), 'test', '->set() accepts keys to partials');

        $m = get_cache_manager($context);
        $m->addCache('module', 'action', get_cache_config(true));
        $m->set('test', '@sf_cache_partial?module=module&action=action');
        $this->is($m->get('@sf_cache_partial?module=module&action=action'), 'test', '->set() accepts keys to contextual partials');

        // ->get()
        $this->diag('->get()');
        $m = get_cache_manager($context);
        $this->is($m->get('module/action'), null, '->get() returns null if the action is not cacheable');
        $m->addCache('module', 'action', get_cache_config());
        $m->set('test', 'module/action');
        $this->is($m->get('module/action'), 'test', '->get() returns the saved content if the action is cacheable');

        // ->has()
        $this->diag('->has()');
        $m = get_cache_manager($context);
        $this->is($m->has('module/action'), false, '->has() returns false if the action is not cacheable');
        $m->addCache('module', 'action', get_cache_config());
        $this->is($m->has('module/action'), false, '->has() returns the cache does not exist for the action');
        $m->set('test', 'module/action');
        $this->is($m->has('module/action'), true, '->get() returns true if the action is in cache');

        // ->remove()
        $this->diag('->remove()');
        $m = get_cache_manager($context);
        $m->addCache('module', 'action', get_cache_config());
        $m->set('test', 'module/action');
        $m->remove('module/action');
        $this->is($m->has('module/action'), false, '->remove() removes cache content for an action');

        $m->set('test', 'module/action?key1=value1');
        $m->set('test', 'module/action?key2=value2');
        $m->remove('module/action?key1=value1');
        $this->is($m->has('module/action?key1=value1'), false, '->remove() removes accepts an internal URI as first parameter');
        $this->is($m->has('module/action?key2=value2'), true, '->remove() does not remove cache content for keys not matching the internal URI');

        $m = get_cache_manager($context);
        $m->addCache('module', 'action', get_cache_config());
        $m->set('test', 'module/action?key1=value1');
        $m->set('test', 'module/action?key1=value2');
        $m->set('test', 'module/action?key2=value1');
        $m->remove('module/action?key1=*');
        $this->is($m->has('module/action?key1=value1'), false, '->remove() accepts wildcards in URIs and then removes all keys matching the pattern');
        $this->is($m->has('module/action?key1=value2'), false, '->remove() accepts wildcards in URIs and then removes all keys matching the pattern');
        $this->is($m->has('module/action?key2=value1'), true, '->remove() accepts wildcards in URIs and lets keys not matching the pattern unchanged');

        $this->diag('Cache key generation options');
        $m = new myViewCacheManager($context, $cache = new myCache2(), array('cache_key_use_vary_headers' => false));
        $this->is($m->generateCacheKey('mymodule/myaction'), '/localhost/mymodule/myaction', '->generateCacheKey() uses "cache_key_use_vary_headers" option to know if vary headers changes cache key.');

        $m = new myViewCacheManager($context, $cache = new myCache2(), array('cache_key_use_host_name' => false));
        $this->is($m->generateCacheKey('mymodule/myaction'), '/all/mymodule/myaction', '->generateCacheKey() uses "cache_key_use_host_name" option to know if vary headers changes cache key.');

        $m = new myViewCacheManager($context, $cache = new myCache2(), array('cache_key_use_host_name' => false, 'cache_key_use_vary_headers' => false));
        $this->is($m->generateCacheKey('mymodule/myaction'), '/mymodule/myaction', '->generateCacheKey() allows the use of both "cache_key_use_host_name" and "cache_key_use_vary_headers" options.');

        $m = new myViewCacheManager($context, new myCache2());
        $this->is($m->generateCacheKey('mymodule/myaction?foo=../_bar'), '/localhost/all/mymodule/myaction/foo/_../__bar', '->generateCacheKey() prevents directory traversal');
        $this->is($m->generateCacheKey('mymodule/myaction?foo=..\\_bar'), '/localhost/all/mymodule/myaction/foo/_..\\__bar', '->generateCacheKey() prevents directory traversal');

        // ->getCurrentCacheKey()
        $this->diag('->getCurrentCacheKey()');
        $m = get_cache_manager($context);
        $this->is($m->getCurrentCacheKey(), 'currentModule/currentAction?currentKey=currentValue&page=5&sort=asc', '->getCurrentCacheKey() appends GET parameters to an existing query string');
        $context->getRouting()->currentInternalUri = 'currentModule/currentAction';
        $this->is($m->getCurrentCacheKey(), 'currentModule/currentAction?page=5&sort=asc', '->getCurrentCacheKey() adds a query string of GET parameters if none is there');
    }
}
