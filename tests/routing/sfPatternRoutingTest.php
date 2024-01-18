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
require_once __DIR__.'/../fixtures/myPatternRouting.php';
require_once __DIR__.'/../fixtures/sfAlwaysAbsoluteRoute.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfPatternRoutingTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    private array $options;
    private myPatternRouting $routing;

    protected function setUp(): void
    {
        $this->options = array(
            'generate_shortest_url' => false,
            'extra_parameters_as_query_string' => false,
        );

        $this->routing = new myPatternRouting(new sfEventDispatcher(), null, $this->options);
    }

    public function testGetRoutes()
    {
        $this->routing->clearRoutes();
        $this->routing->connect('test1', new sfRoute('/:module/:action'));
        $this->routing->connect('test2', new sfRoute('/home'));
        $routes = $this->routing->getRoutes();

        $this->assertSame(2, count($routes), '->getRoutes() returns all current routes');
        $this->assertSame(true, isset($routes['test1']), '->getRoutes() returns a hash indexed by route names');
        $this->assertSame(true, isset($routes['test2']), '->getRoutes() returns a hash indexed by route names');
    }

    public function testSetRoutes()
    {
        $this->routing->connect('test1', new sfRoute('/:module/:action'));
        $this->routing->connect('test2', new sfRoute('/home'));
        $routes = $this->routing->getRoutes();
        $this->routing->clearRoutes();
        $this->routing->setRoutes($routes);
        $this->assertSame($routes, $this->routing->getRoutes(), '->setRoutes() takes a routes array as its first parameter');
    }

    public function testClearRoutes()
    {
        $this->routing->connect('test1', new sfRoute('/:module/:action'));
        $this->routing->clearRoutes();
        $routes = $this->routing->getRoutes();
        $this->assertSame(0, count($routes), '->clearRoutes() clears all current routing rules');
    }

    public function testHasRoutes()
    {
        $this->assertSame(false, $this->routing->hasRoutes(), '->hasRoutes() returns false if there is no route');
        $this->routing->connect('test1', new sfRoute('/:module/:action'));
        $this->assertSame(true, $this->routing->hasRoutes(), '->hasRoutes() returns true if some routes are registered');
    }

    public function testConnect()
    {
        $this->routing->connect('test', new sfRoute(':module/:action', array('module' => 'default', 'action' => 'index')));
        $this->routing->connect('test1', new sfRoute('', array('module' => 'default', 'action' => 'index')));

        $routes = $this->routing->getRoutes();

        $this->assertSame('/:module/:action', $routes['test']->getPattern(), '->connect() automatically adds trailing / to route if missing');
        $this->assertSame('/', $routes['test1']->getPattern(), '->connect() detects empty routes');
    }

    public function testSimpleRoutes()
    {
        $this->routing->connect('test1', new sfRoute('/:module/:action', array('module' => 'default', 'action' => 'index1')));
        $this->routing->connect('test2', new sfRoute('/foo/bar', array('module' => 'default', 'action' => 'index2')));
        $this->routing->connect('test3', new sfRoute('/foo/:module/bar/:action', array('module' => 'default', 'action' => 'index3')));
        $this->routing->connect('test4', new sfRoute('/nodefault/:module/:action'));

        $params = array('module' => 'default', 'action' => 'index1');
        $url = '/default/index1';
        $this->assertSame($params, $this->routing->parse($url), '->parse() /:module/:action route');
        $this->assertSame($url, $this->routing->generate('', $params), '->generate() /:module/:action url');
    }

    public function testRouteOrder()
    {
        $this->routing->connect('test', new sfRoute('/test/:id', array('module' => 'default1', 'action' => 'index1'), array('id' => '\d+')));
        $this->routing->connect('test1', new sfRoute('/test/:id', array('module' => 'default2', 'action' => 'index2')));
        $params = array('module' => 'default1', 'action' => 'index1', 'id' => '12');
        $url = '/test/12';
        $this->assertSame($params, $this->routing->parse($url), '->parse() takes the first matching route');
        $this->assertSame($url, $this->routing->generate('', $params), '->generate() takes the first matching route');

        $params = array('module' => 'default2', 'action' => 'index2', 'id' => 'foo');
        $url = '/test/foo';
        $this->assertSame($params, $this->routing->parse($url), '->parse() takes the first matching route');
        $this->assertSame($url, $this->routing->generate('', $params), '->generate() takes the first matching route');

        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action/test/:id/:test', array('module' => 'default', 'action' => 'index')));
        $this->routing->connect('test1', new sfRoute('/:module/:action/test/:id', array('module' => 'default', 'action' => 'index', 'id' => 'foo')));
        $params = array('module' => 'default', 'action' => 'index', 'id' => 'foo');
        $url = '/default/index/test/foo';
        $this->assertSame($params, $this->routing->parse($url), '->parse() takes the first matching route');
        $this->assertSame($url, $this->routing->generate('', $params), '->generate() takes the first matching route');
    }

    public function testSuffix()
    {
        $routing = new myPatternRouting(new sfEventDispatcher(), null, array_merge($this->options, array('suffix' => '.html')));
        $routing->connect('foo0', new sfRoute('/foo0/:module/:action/:param0', array('module' => 'default', 'action' => 'index0')));
        $url0 = '/foo0/default/index0/foo0.html';
        $routing->connect('foo1', new sfRoute('/foo1/:module/:action/:param1.', array('module' => 'default', 'action' => 'index1')));
        $url1 = '/foo1/default/index1/foo1';
        $routing->connect('foo2', new sfRoute('/foo2/:module/:action/:param2/', array('module' => 'default', 'action' => 'index2')));
        $url2 = '/foo2/default/index2/foo2/';
        $routing->connect('foo3', new sfRoute('/foo3/:module/:action/:param3.foo', array('module' => 'default', 'action' => 'index3')));
        $url3 = '/foo3/default/index3/foo3.foo';
        $routing->connect('foo4', new sfRoute('/foo4/:module/:action/:param4.:param_5', array('module' => 'default', 'action' => 'index4')));
        $url4 = '/foo4/default/index4/foo.bar';

        $this->assertSame($url0, $routing->generate('', array('module' => 'default', 'action' => 'index0', 'param0' => 'foo0')), '->generate() creates URL suffixed by "sf_suffix" parameter');
        $this->assertSame($url1, $routing->generate('', array('module' => 'default', 'action' => 'index1', 'param1' => 'foo1')), '->generate() creates URL with no suffix when route ends with .');
        $this->assertSame($url2, $routing->generate('', array('module' => 'default', 'action' => 'index2', 'param2' => 'foo2')), '->generate() creates URL with no suffix when route ends with /');
        $this->assertSame($url3, $routing->generate('', array('module' => 'default', 'action' => 'index3', 'param3' => 'foo3')), '->generate() creates URL with special suffix when route ends with .suffix');
        $this->assertSame($url4, $routing->generate('', array('module' => 'default', 'action' => 'index4', 'param4' => 'foo', 'param_5' => 'bar')), '->generate() creates URL with no special suffix when route ends with .:suffix');

        $this->assertSame(array('module' => 'default', 'action' => 'index0', 'param0' => 'foo0'), $routing->parse($url0), '->parse() finds route from URL suffixed by "sf_suffix"');
        $this->assertSame(array('module' => 'default', 'action' => 'index1', 'param1' => 'foo1'), $routing->parse($url1), '->parse() finds route with no suffix when route ends with .');
        $this->assertSame(array('module' => 'default', 'action' => 'index2', 'param2' => 'foo2'), $routing->parse($url2), '->parse() finds route with no suffix when route ends with /');
        $this->assertSame(array('module' => 'default', 'action' => 'index3',  'param3' => 'foo3'), $routing->parse($url3), '->parse() finds route with special suffix when route ends with .suffix');
        $this->assertSame(array('module' => 'default', 'action' => 'index4',  'param4' => 'foo', 'param_5' => 'bar'), $routing->parse($url4), '->parse() finds route with special suffix when route ends with .:suffix');
    }

    public function testQueryString()
    {
        $this->routing->connect('test', new sfRoute('/index.php/:module/:action', array('module' => 'default', 'action' => 'index')));
        $params = array('module' => 'default', 'action' => 'index');
        $url = '/index.php/default/index?test=1&toto=2';
        $this->assertSame($params, $this->routing->parse($url), '->parse() does not take query string into account');
    }

    public function testDefaultValues()
    {
        $this->routing->connect('test', new sfRoute('/:module/:action', array('module' => 'default', 'action' => 'index')));
        $this->assertSame('/default/index', $this->routing->generate('', array('module' => 'default')),
            '->generate() creates URL for route with missing parameter if parameter is set in the default values');
        $this->assertSame(array('module' => 'default', 'action' => 'index'), $this->routing->parse('/default'),
            '->parse() finds route for URL   with missing parameter if parameter is set in the default values');

        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action/:foo', array('module' => 'default', 'action' => 'index', 'foo' => 'bar')));
        $this->assertSame('/default/index/bar', $this->routing->generate('', array('module' => 'default')),
            '->generate() creates URL for route with more than one missing parameter if default values are set');
        $this->assertSame(array('module' => 'default', 'action' => 'index', 'foo' => 'bar'), $this->routing->parse('/default'),
            '->parse() finds route for URL   with more than one missing parameter if default values are set');

        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action', array('module' => 'default', 'action' => 'index')));
        $params = array('module' => 'foo', 'action' => 'bar');
        $url = '/foo/bar';
        $this->assertSame($url, $this->routing->generate('', $params), '->generate() parameters override the route default values');
        $this->assertSame($params, $this->routing->parse($url), '->parse() finds route with parameters distinct from the default values');

        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action', array('module' => 'default')));
        $params = array('module' => 'default', 'action' => 'index');
        $url = '/default/index';
        $this->assertSame($url, $this->routing->generate('', $params), '->generate() creates URL even if there is no default value');
        $this->assertSame($params, $this->routing->parse($url), '->parse() finds route even when route has no default value');
    }

    public function testCombinedExamples()
    {
        // meg kell cserelni az exceptedet es az actualt
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action/:test/:id', array('module' => 'default', 'action' => 'index', 'id' => 'toto')));
        $params = array('module' => 'default', 'action' => 'index', 'id' => 'bar', 'test' => 'foo');
        $url = '/default/index/foo/bar';
        $this->is($this->routing->generate('', $params), $url, '->generate() routes have default parameters value that can be overriden');
        $this->is($this->routing->parse($url), $params, '->parse() routes have default parameters value that can be overriden');
        $params = array('module' => 'default', 'action' => 'index', 'id' => 'toto', 'test' => 'foo');
        $url = '/default/index/foo';
        $this->assertNotSame($url, $this->routing->generate('', $params), '->generate() does not remove the last parameter if the parameter is default value');
        $this->is($this->routing->parse($url), $params, '->parse() removes the last parameter if the parameter is default value');

        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action/:test/:id', array('module' => 'default', 'action' => 'index', 'test' => 'foo', 'id' => 'bar')));
        $params = array('module' => 'default', 'action' => 'index', 'test' => 'foo', 'id' => 'bar');
        $url = '/default/index';
        $this->assertNotSame($url, $this->routing->generate('', $params), '->generate() does not remove last parameters if they have default values');
        $this->is($this->routing->parse($url), $params, '->parse() removes last parameters if they have default values');
    }

    public function testRoutingDefaultsParameters()
    {
        $this->routing->setDefaultParameter('foo', 'bar');
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/test/:foo/:id', array('module' => 'default', 'action' => 'index')));
        $params = array('module' => 'default', 'action' => 'index', 'id' => 12);
        $url = '/test/bar/12';
        $this->is($this->routing->generate('', $params), $url, '->generate() merges parameters with defaults from "sf_routing_defaults"');
        $this->routing->setDefaultParameters(array());
    }

    public function testUnnamedWildcard()
    {
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action/test/*', array('module' => 'default', 'action' => 'index')));
        $params = array('module' => 'default', 'action' => 'index');
        $url = '/default/index/test';
        $this->is($this->routing->parse($url), $params, '->parse() finds route for URL   with no additional parameters when route ends with unnamed wildcard *');
        $this->is($this->routing->generate('', $params), $url, '->generate() creates URL for route with no additional parameters when route ends with unnamed wildcard *');
    }

    public function testTicket4173()
    {
        $params = array(
            'module' => 'default',
            'action' => 'index',
        );

        $url = '/default/index/test/';
        $this->routing->connect('test', new sfRoute('/:module/:action/test/*', array('module' => 'default', 'action' => 'index')));
        $this->is($this->routing->parse($url), $params, '->parse() finds route for URL   with no additional parameters and trailing slash when route ends with unnamed wildcard *');
        $params = array('module' => 'default', 'action' => 'index', 'titi' => 'toto');
        $url = '/default/index/test/titi/toto/';
        $this->is($this->routing->parse($url), $params, '->parse() finds route for URL   with additional parameters and trailing slash when route ends with unnamed wildcard *');

        $params = array('module' => 'default', 'action' => 'index', 'page' => '4.html', 'toto' => '1', 'titi' => 'toto', 'OK' => '1'); // test modified: toto and OK was true
        $url = '/default/index/test/page/4.html/toto/1/titi/toto/OK/1';
        $this->is($this->routing->parse($url), $params, '->parse() finds route for URL   with additional parameters when route ends with unnamed wildcard *');
        $this->is($this->routing->generate('', $params), $url, '->generate() creates URL for route with additional parameters when route ends with unnamed wildcard *');
        $this->is($this->routing->parse('/default/index/test/page/4.html/toto/1/titi/toto/OK/1/module/test/action/tutu'), $params, '->parse() does not override named wildcards with parameters passed in unnamed wildcard *');
        $this->is($this->routing->parse('/default/index/test/page/4.html////toto//1/titi//toto//OK/1'), $params, '->parse() considers multiple separators as single in unnamed wildcard *');
    }

    public function testUnnamedWildcardAfterAToken()
    {
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module', array('action' => 'index')));
        $this->routing->connect('test1', new sfRoute('/:module/:action/*', array()));
        $params = array('module' => 'default', 'action' => 'index', 'toto' => 'titi');
        $url = '/default/index/toto/titi';
        $this->is($this->routing->parse($url), $params, '->parse() takes the first matching route but takes * into accounts');
        $this->is($this->routing->generate('', $params), $url, '->generate() takes the first matching route but takes * into accounts');
        $params = array('module' => 'default', 'action' => 'index');
        $url = '/default';
        $this->is($this->routing->parse($url), $params, '->parse() takes the first matching route but takes * into accounts');
        $this->is($this->routing->generate('', $params), $url, '->generate() takes the first matching route but takes * into accounts');
    }

    public function testUnnamedWildcardInTheMiddleOfARule()
    {
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action/*/test', array('module' => 'default', 'action' => 'index')));

        $params = array('module' => 'default', 'action' => 'index');
        $url = '/default/index/test';
        $this->is($this->routing->parse($url), $params, '->parse() finds route for URL when no extra parameters are present in the URL');
        $this->is($this->routing->generate('', $params), $url, '->generate() creates URL for route when no extra parameters are added to the internal URI');

        $params = array('module' => 'default', 'action' => 'index', 'foo' => '1', 'bar' => 'foobar'); // test modified: foo was true
        $url = '/default/index/foo/1/bar/foobar/test';
        $this->is($this->routing->parse($url), $params, '->parse() finds route for URL when extra parameters are present in the URL');
        $this->is($this->routing->generate('', $params), $url, '->generate() creates URL for route when extra parameters are added to the internal URI');
    }

    public function testUnnamedWildcardInTheMiddleOfARuleWithASeparatorAfterDistinctFromSlash()
    {
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action/*.test', array('module' => 'default', 'action' => 'index')));

        $params = array('module' => 'default', 'action' => 'index');
        $url = '/default/index.test';
        $this->is($this->routing->parse($url), $params, '->parse() finds route for URL when no extra parameters are present in the URL');
        $this->is($this->routing->generate('', $params), $url, '->generate() creates URL for route when no extra parameters are added to the internal URI');

        $params = array('module' => 'default', 'action' => 'index', 'foo' => '1', 'bar' => 'foobar'); // test modified: foo was true
        $url = '/default/index/foo/1/bar/foobar.test';
        $this->is($this->routing->parse($url), $params, '->parse() finds route for URL when extra parameters are present in the URL');
        $this->is($this->routing->generate('', $params), $url, '->generate() creates URL for route when extra parameters are added to the internal URI');
    }

    public function testRequirements()
    {
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action/id/:id', array('module' => 'default', 'action' => 'integer'), array('id' => '\d+')));
        $this->routing->connect('test1', new sfRoute('/:module/:action/:id', array('module' => 'default', 'action' => 'string')));

        $params = array('module' => 'default', 'action' => 'integer', 'id' => '12'); // test modified: id was 12 integer
        $url = '/default/integer/id/12';
        $this->is($this->routing->parse($url), $params, '->parse() finds route for URL   when parameters meet requirements');
        $this->is($this->routing->generate('', $params), $url, '->generate() creates URL for route when parameters meet requirements');

        $params = array('module' => 'default', 'action' => 'string', 'id' => 'NOTANINTEGER');
        $url = '/default/string/NOTANINTEGER';
        $this->is($this->routing->parse($url), $params, '->parse() ignore routes when parameters don\'t meet requirements');
        $this->is($this->routing->generate('', $params), $url, '->generate() ignore routes when parameters don\'t meet requirements');

        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module/:action/id/:id', array('module' => 'default', 'action' => 'integer'), array('id' => '[^/]{2}')));

        $params = array('module' => 'default', 'action' => 'integer', 'id' => 'a1');
        $url = '/default/integer/id/a1';
        $this->is($this->routing->parse($url), $params, '->parse() finds route for URL   when parameters meet requirements');
        $this->is($this->routing->generate('', $params), $url, '->generate() creates URL for route when parameters meet requirements');
    }

    public function testSeparators()
    {
        $options = array(
            'generate_shortest_url' => false,
            'extra_parameters_as_query_string' => false,
        );

        $routing = new myPatternRouting(new sfEventDispatcher(), null, array_merge($options, array('segment_separators' => array('/', ';', ':', '|', '.', '-', '+'))));
        $routing->connect('test', new sfRoute('/:module/:action;:foo::baz+static+:toto|:hip-:zozo.:format', array()));
        $routing->connect('test0', new sfRoute('/:module/:action0', array()));
        $routing->connect('test1', new sfRoute('/:module;:action1', array()));
        $routing->connect('test2', new sfRoute('/:module::action2', array()));
        $routing->connect('test3', new sfRoute('/:module+:action3', array()));
        $routing->connect('test4', new sfRoute('/:module|:action4', array()));
        $routing->connect('test5', new sfRoute('/:module.:action5', array()));
        $routing->connect('test6', new sfRoute('/:module-:action6', array()));
        $params = array('module' => 'default', 'action' => 'index', 'action0' => 'foobar');
        $url = '/default/foobar';
        $this->is($routing->parse($url), $params, '->parse() recognizes parameters separated by /');
        $this->is($routing->generate('', $params), $url, '->generate() creates routes with / separator');
        $params = array('module' => 'default', 'action' => 'index', 'action1' => 'foobar');
        $url = '/default;foobar';
        $this->is($routing->parse($url), $params, '->parse() recognizes parameters separated by ;');
        $this->is($routing->generate('', $params), $url, '->generate() creates routes with ; separator');
        $params = array('module' => 'default', 'action' => 'index', 'action2' => 'foobar');
        $url = '/default:foobar';
        $this->is($routing->parse($url), $params, '->parse() recognizes parameters separated by :');
        $this->is($routing->generate('', $params), $url, '->generate() creates routes with : separator');
        $params = array('module' => 'default', 'action' => 'index', 'action3' => 'foobar');
        $url = '/default+foobar';
        $this->is($routing->parse($url), $params, '->parse() recognizes parameters separated by +');
        $this->is($routing->generate('', $params), $url, '->generate() creates routes with + separator');
        $params = array('module' => 'default', 'action' => 'index', 'action4' => 'foobar');
        $url = '/default|foobar';
        $this->is($routing->parse($url), $params, '->parse() recognizes parameters separated by |');
        $this->is($routing->generate('', $params), $url, '->generate() creates routes with | separator');
        $params = array('module' => 'default', 'action' => 'index', 'action5' => 'foobar');
        $url = '/default.foobar';
        $this->is($routing->parse($url), $params, '->parse() recognizes parameters separated by .');
        $this->is($routing->generate('', $params), $url, '->generate() creates routes with . separator');
        $params = array('module' => 'default', 'action' => 'index', 'action' => 'index', 'action6' => 'foobar');
        $url = '/default-foobar';
        $this->is($routing->parse($url), $params, '->parse() recognizes parameters separated by -');
        $this->is($routing->generate('', $params), $url, '->generate() creates routes with - separator');
        $params = array('module' => 'default', 'action' => 'index', 'action' => 'foobar', 'foo' => 'bar', 'baz' => 'baz', 'toto' => 'titi', 'hip' => 'hop', 'zozo' => 'zaza', 'format' => 'xml');
        $url = '/default/foobar;bar:baz+static+titi|hop-zaza.xml';
        $this->is($routing->parse($url), $params, '->parse() recognizes parameters separated by mixed separators');
        $this->is($routing->generate('', $params), $url, '->generate() creates routes with mixed separators');
    }

    public function testTicket8114()
    {
        $options = array(
            'generate_shortest_url' => false,
            'extra_parameters_as_query_string' => false,
        );

        $routing = new myPatternRouting(new sfEventDispatcher(), null, array_merge($options, array('segment_separators' => array())));
        $routing->connect('nosegment', new sfRoute('/:nonsegmented', array()));
        $params = array('module' => 'default', 'action' => 'index', 'nonsegmented' => 'plainurl');
        $url = '/plainurl';
        $this->is($routing->parse($url), $params, '->parse() works without segment_separators');
        $this->is($routing->generate('', $params), $url, '->generate() works without segment_separators');
        $params = array('module' => 'default', 'action' => 'index', 'nonsegmented' => 'foo/bar/baz');
        $this->is($routing->parse('/foo/bar/baz'), $params, '->parse() works without segment_separators');
        $this->is($routing->generate('', $params), '/foo%2Fbar%2Fbaz', '->generate() works without segment_separators');

        $routing = new myPatternRouting(new sfEventDispatcher(), null, array_merge($options, array('segment_separators' => array('~'))));
        $routing->connect('nosegment', new sfRoute('/:nonsegmented', array()));
        $params = array('module' => 'default', 'action' => 'index', 'nonsegmented' => 'plainurl');
        $url = '/plainurl';
        $this->is($routing->parse($url), $params, '->parse() works with segment_separators which are not in url');
        $this->is($routing->generate('', $params), $url, '->generate() works with segment_separators which are not in url');
        $params = array('module' => 'default', 'action' => 'index', 'nonsegmented' => 'foo/bar/baz');
        $this->is($routing->parse('/foo/bar/baz'), $params, '->parse() works with segment_separators which are not in url');
        $this->is($routing->generate('', $params), '/foo%2Fbar%2Fbaz', '->generate() works with segment_separators which are not in url');
    }

    public function testTokenNames()
    {
        $this->routing->clearRoutes();
        $this->routing->connect('test1', new sfRoute('/:foo_1/:bar2', array()));
        $params = array('module' => 'default', 'action' => 'index', 'foo_1' => 'test', 'bar2' => 'foobar');
        $url = '/test/foobar';
        $this->is($this->routing->parse($url), $params, '->parse() accepts token names composed of letters, digits and _');
        $this->is($this->routing->generate('', $params), $url, '->generate() accepts token names composed of letters, digits and _');
    }

    public function testTodoMigrate()
    {
        $options = array(
            'generate_shortest_url' => false,
            'extra_parameters_as_query_string' => false,
        );

        $routing = new myPatternRouting(new sfEventDispatcher(), null, array_merge($options, array('variable_prefixes' => array(':', '$'))));

        // token prefix
        $this->diag('token prefix');
        $routing->clearRoutes();
        $routing->connect('test2', new sfRoute('/2/$module/$action/$id', array()));
        $routing->connect('test3', new sfRoute('/3/$module/:action/$first_name/:last_name', array()));
        $routing->connect('test1', new sfRoute('/1/:module/:action', array()));
        $params1 = array('module' => 'foo', 'action' => 'bar');
        $url1 = '/1/foo/bar';
        $this->is($routing->parse($url1), $params1, '->parse() accepts token names starting with :');
        $this->is($routing->generate('', $params1), $url1, '->generate() accepts token names starting with :');
        $params2 = array('module' => 'foo', 'action' => 'bar', 'id' => '12'); // test modified: id was 12 integer
        $url2 = '/2/foo/bar/12';
        $this->is($routing->parse($url2), $params2, '->parse() accepts token names starting with $');
        $this->is($routing->generate('', $params2), $url2, '->generate() accepts token names starting with $');
        $params3 = array('module' => 'foo', 'action' => 'bar', 'first_name' => 'John', 'last_name' => 'Doe');
        $url3 = '/3/foo/bar/John/Doe';
        $this->is($routing->parse($url3), $params3, '->parse() accepts token names starting with mixed : and $');
        $this->is($routing->generate('', $params3), $url3, '->generate() accepts token names starting with mixed : and $');

        // named routes
        $this->diag('named routes');
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/test/:id', array('module' => 'default', 'action' => 'integer'), array('id' => '\d+')));
        $params = array('module' => 'default', 'action' => 'integer', 'id' => 12);
        $url = '/test/12';
        $named_params = array('id' => 12);
        $this->is($this->routing->generate('', $params), $url, '->generate() can take an empty route name as its first parameter');
        $this->is($this->routing->generate('test', $params), $url, '->generate() can take a route name as its first parameter');
        $this->is($this->routing->generate('test', $named_params), $url, '->generate() with named routes needs only parameters not defined in route default');

        // ->appendRoute()
        $this->diag('->appendRoute()');
        $this->routing->clearRoutes();
        $this->routing->connect('test', $route1 = new sfRoute('/:module', array('action' => 'index')));
        $this->routing->connect('test1', $route2 = new sfRoute('/:module/:action/*', array()));
        $routes = $this->routing->getRoutes();
        $this->routing->clearRoutes();
        $this->routing->appendRoute('test', $route1);
        $this->routing->appendRoute('test1', $route2);
        $this->is($this->routing->getRoutes(), $routes, '->appendRoute() is an alias for ->connect()');

        // ->prependRoute()
        $this->diag('->prependRoute()');
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/:module', array('action' => 'index')));
        $this->routing->connect('test1', new sfRoute('/:module/:action/*', array()));
        $route_names = array_keys($this->routing->getRoutes());
        $this->routing->clearRoutes();
        $this->routing->prependRoute('test', new sfRoute('/:module', array('action' => 'index')));
        $this->routing->prependRoute('test1', new sfRoute('/:module/:action/*', array()));
        $p_route_names = array_keys($this->routing->getRoutes());
        $this->is(implode('-', $p_route_names), implode('-', array_reverse($route_names)), '->prependRoute() adds new routes at the beginning of the existings ones');

        // ->addRouteBefore()
        $this->diag('->insertRouteBefore()');
        $this->routing->clearRoutes();
        $this->routing->connect('test1', new sfRoute('/:module', array('action' => 'index')));
        $this->routing->connect('test3', new sfRoute('/:module/:action/*', array()));
        $this->routing->insertRouteBefore('test3', 'test2', new sfRoute('/:module/:action', array('module' => 'default')));
        $route_names = array_keys($this->routing->getRoutes());
        $this->routing->clearRoutes();
        $this->routing->connect('test1', new sfRoute('/:module', array('action' => 'index')));
        $this->routing->connect('test2', new sfRoute('/:module/:action', array('module' => 'default')));
        $this->routing->connect('test3', new sfRoute('/:module/:action/*', array()));
        $test_route_names = array_keys($this->routing->getRoutes());
        $this->is(implode('-', $test_route_names), implode('-', $route_names), '->insertRouteBefore() adds a new route before another existings one');
        $this->routing->clearRoutes();
        $msg = '->insertRouteBefore() throws an sfConfigurationException when trying to insert a route before a non existent one';
        try {
            $this->routing->insertRouteBefore('test2', 'test', new sfRoute('/index.php/:module/:action', array('module' => 'default', 'action' => 'index')));
            $this->fail($msg);
        } catch (sfConfigurationException $e) {
            $this->pass($msg);
        }

// ->getCurrentInternalUri()
        $this->diag('->getCurrentInternalUri()');
        $this->routing->clearRoutes();
        $this->routing->connect('test2', new sfRoute('/module/action/:id', array('module' => 'foo', 'action' => 'bar')));
        $this->routing->connect('test', new sfRoute('/:module', array('action' => 'index')));
        $this->routing->connect('test1', new sfRoute('/:module/:action/*', array()));
        $this->routing->connect('test3', new sfRoute('/', array()));
        $this->routing->parse('/');
        $this->is($this->routing->getCurrentInternalUri(), 'default/index', '->getCurrentInternalUri() returns the internal URI for last parsed URL');
        $this->routing->parse('/foo/bar/bar/foo/a/b');
        $this->is($this->routing->getCurrentInternalUri(), 'foo/bar?a=b&bar=foo', '->getCurrentInternalUri() returns the internal URI for last parsed URL');
        $this->routing->parse('/module/action/2');
        $this->is($this->routing->getCurrentInternalUri(true), '@test2?id=2', '->getCurrentInternalUri() returns the internal URI for last parsed URL');

        // Lazy routes config cache
        $this->diag('Lazy Routes Config Cache');
        $dispatcher = new sfEventDispatcher();
        $dispatcher->connect('routing.load_configuration', 'configureRouting');
        function configureRouting($event)
        {
            $event->getSubject()->connect('first', new sfRoute('/first'));
            $event->getSubject()->connect('second', new sfRoute('/', array()));
        }

        // these tests are against r7363
        $this->is($this->routing->getCurrentInternalUri(false), 'foo/bar?id=2', '->getCurrentInternalUri() returns the internal URI for last parsed URL');
        $this->is($this->routing->getCurrentInternalUri(true), '@test2?id=2', '->getCurrentInternalUri() returns the internal URI for last parsed URL');
        $this->is($this->routing->getCurrentInternalUri(false), 'foo/bar?id=2', '->getCurrentInternalUri() returns the internal URI for last parsed URL');

        // regression for ticket #3423  occuring when cache is used. (for the test its enough to have it non null)
        $rCached = new myPatternRouting(new sfEventDispatcher(), new sfNoCache(), $options);
        $rCached->connect('test', new sfRoute('/:module', array('action' => 'index')));
        $rCached->connect('test2', new sfRoute('/', array()));
        $rCached->parse('/');
        $this->is($rCached->getCurrentInternalUri(), 'default/index', '->getCurrentInternalUri() returns the internal URI for last parsed URL using cache');
        $rCached->parse('/test');
        $this->is($rCached->getCurrentInternalUri(), 'test/index', '->getCurrentInternalUri() returns the internal URI for last parsed URL using cache');
        $rCached->parse('/');
        $this->is($rCached->getCurrentInternalUri(), 'default/index', '->getCurrentInternalUri() returns the internal URI for last parsed URL using cache');
        // findRoute was added to be the side effectless version to check an uri
        $parameters = $rCached->findRoute('/test');
        $this->is($parameters,
            array('name' => 'test', 'pattern' => '/:module', 'parameters' => array('module' => 'test', 'action' => 'index')),
            '->findRoute() returns information about matching route');
        $this->is($rCached->getCurrentInternalUri(), 'default/index', '->findRoute() does not change the internal URI of sfPatternRouting');
        $this->is($rCached->findRoute('/no/match/found'), false, '->findRoute() returns false on non-matching route');

        // current internal uri is reset after negative match
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/test', array('bar' => 'foo')));
        $this->routing->parse('/test');
        $this->routing->parse('/notfound');
        $this->is($this->routing->getCurrentInternalUri(), null, '->getCurrentInternalUri() reseted after negative match');
        $this->is($this->routing->getCurrentRouteName(), null, '->getCurrentRouteName() reseted after negative match');

        // defaults
        $this->diag('defaults');
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/test', array('bar' => 'foo')));
        $params = array('module' => 'default', 'action' => 'index');
        $url = '/test';
        $this->is($this->routing->generate('', $params), $url, '->generate() routes takes default values into account when matching a route');
        $params = array('module' => 'default', 'action' => 'index', 'bar' => 'foo');
        $this->is($this->routing->generate('', $params), $url, '->generate() routes takes default values into account when matching a route');
        $params = array('module' => 'default', 'action' => 'index', 'bar' => 'bar');
        try {
            $this->routing->generate('', $params);
            $this->fail('->generate() throws a sfConfigurationException if no route matches the params');
        } catch (sfConfigurationException $e) {
            $this->pass('->generate() throws a sfConfigurationException if no route matches the params');
        }

// mandatory parameters
        $this->diag('mandatory parameters');
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/test/:foo/:bar'));
        $params = array('foo' => 'bar');
        try {
            $this->routing->generate('test', $params);
            $this->fail('->generate() throws a InvalidArgumentException if some mandatory parameters are not provided');
        } catch (InvalidArgumentException $e) {
            $this->pass('->generate() throws a InvalidArgumentException if some mandatory parameters are not provided');
        }

// default module/action overriding
        $this->diag('module/action overriding');
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/', array('module' => 'default1', 'action' => 'default1')));
        $params = array('module' => 'default1', 'action' => 'default1');
        $this->is($this->routing->parse('/'), $params, '->parse() overrides the default module/action if provided in the defaults');
        $this->is($this->routing->generate('', $params), '/', '->generate() overrides the default module/action if provided in the defaults');

        // parameter values decoding
        $this->diag('parameter values decoding');
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/test/:value', array('module' => 'default', 'action' => 'index')));
        $this->routing->connect('test1', new sfRoute('/test1/*', array('module' => 'default', 'action' => 'index')));
        $this->is($this->routing->parse('/test/test%26foo%3Dbar%2Bfoo'), array('module' => 'default', 'action' => 'index', 'value' => 'test&foo=bar+foo'), '->parse() decodes parameter values');
        $this->is($this->routing->parse('/test1/value/test%26foo%3Dbar%2Bfoo'), array('module' => 'default', 'action' => 'index', 'value' => 'test&foo=bar+foo'), '->parse() decodes parameter values');

        // feature change bug from sf1.0 - ticket #3090
        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/customer/:param1/:action/*', array('module' => 'default')));
        $this->routing->connect('default', new sfRoute('/:module/:action'));
        $url = '/customer/create';
        $params = array('module' => 'customer', 'action' => 'create');
        $this->is($this->routing->parse($url), $params, '->parse() /:module/:action route');

        $url = '/customer/param1/action';
        $params = array('module' => 'default', 'action' => 'action', 'param1' => 'param1');
        $this->is($this->routing->parse($url), $params, '->parse() /customer/:param1/:action/* route');

        $this->routing->clearRoutes();
        $this->routing->connect('test', new sfRoute('/customer/:id/:id_name', array('module' => 'default')));
        $this->is($this->routing->generate('', array('id' => 2, 'id_name' => 'fabien')), '/customer/2/fabien', '->generate() first replaces the longest variable names');

        $this->routing->clearRoutes();
        $this->routing->connect('default', new sfAlwaysAbsoluteRoute('/:module/:action'));
        $this->is($this->routing->generate('', array('module' => 'foo', 'action' => 'bar')), 'http://localhost/foo/bar', '->generate() allows route to generate absolute urls');
        $this->is($this->routing->generate('', array('module' => 'foo', 'action' => 'bar'), true), 'http://localhost/foo/bar', '->generate() does not double-absolutize urls');

        $this->diag('suffix handling with generate_shortest_url option');

        $routing = new myPatternRouting(new sfEventDispatcher(), null, array('generate_shortest_url' => true, 'extra_parameters_as_query_string' => false, 'suffix' => '.html'));
        $routing->connect('test2', new sfRoute('/users/:username/:sort/:start/', array('module' => 'user', 'action' => 'show', 'sort' => 'all', 'start' => '0'), array('requirements' => array('username' => '\w+', 'start' => '\d+'))));
        $this->is($routing->generate('', array('username' => 'test1', 'module' => 'user', 'action' => 'show')), '/users/test1/', '->generate() creates URL when using suffix and generate_shortest_url');
        $this->is($routing->generate('', array('username' => 'test1', 'module' => 'user', 'action' => 'show', 'sort' => 'all', 'start' => '1')), '/users/test1/all/1/', '->generate() creates URL when using suffix and generate_shortest_url');
        $this->is($routing->parse('/users/test1/'), array('module' => 'user', 'action' => 'show', 'sort' => 'all', 'start' => '0', 'username' => 'test1'), '->parse() returns all default parameters when provided suffix and generate_shortest_url enabled with / suffix');

        $routing = new myPatternRouting(new sfEventDispatcher(), null, array('generate_shortest_url' => true, 'extra_parameters_as_query_string' => false, 'suffix' => '.html'));
        $routing->connect('test1', new sfRoute('/users/:username/:sort/:start', array('module' => 'user', 'action' => 'show', 'sort' => 'all', 'start' => '0'), array('requirements' => array('username' => '\w+', 'start' => '\d+'))));
        $this->is($routing->generate('', array('username' => 'test1', 'module' => 'user', 'action' => 'show')), '/users/test1.html', '->generate() creates URL when using suffix and generate_shortest_url');
        $this->is($routing->generate('', array('username' => 'test1', 'module' => 'user', 'action' => 'show', 'sort' => 'all', 'start' => '0')), '/users/test1.html', '->generate() creates URL when using suffix and generate_shortest_url');
        $this->is($routing->generate('', array('username' => 'test1', 'module' => 'user', 'action' => 'show', 'sort' => 'all', 'start' => '1')), '/users/test1/all/1.html', '->generate() creates URL when using suffix and generate_shortest_url');

        $this->is($routing->parse('/users/test1.html'), array('module' => 'user', 'action' => 'show', 'sort' => 'all', 'start' => '0', 'username' => 'test1'), '->parse() returns all default parameters when provided suffix and generate_shortest_url enabled with .html suffix');

        $routing = new myPatternRouting(new sfEventDispatcher(), null, array('generate_shortest_url' => true, 'extra_parameters_as_query_string' => false, 'suffix' => '.html'));
        $routing->connect('posts', new sfRoute('/posts', array('module' => 'posts', 'action' => 'index', 'page' => '1')));
        $routing->connect('posts_pages', new sfRoute('/posts/:page', array('module' => 'posts', 'action' => 'index', 'page' => '1')));

        $this->is($routing->generate('', array('module' => 'posts', 'action' => 'index')), '/posts.html', '->generate() creates URL when using suffix and generate_shortest_url');
        $this->is($routing->generate('', array('module' => 'posts', 'action' => 'index', 'page' => '1')), '/posts.html', '->generate() creates URL when using suffix and generate_shortest_url');
        $this->is($routing->generate('', array('module' => 'posts', 'action' => 'index', 'page' => '2')), '/posts/2.html', '->generate() creates URL when using suffix and generate_shortest_url');

        $this->diag('load_configuration with serialized routes');

        // see fixtures/config_routing.yml.php
        $routing = new myPatternRouting(new sfEventDispatcher(), new sfNoCache(), array('load_configuration' => true));
        $this->ok($routing->hasRouteName('test1'), '->loadConfiguration() Config file is loaded');
        $routes = $routing->getRoutes();
        $this->ok(is_string($routes['test1']), '->loadConfiguration() Route objects are not serialized in cache');
        $route = $routing->getRoute('test1');
        $this->ok(is_object($route), '->loadConfiguration() Route objects are unserialized on demand');
        $this->is_deeply($routing->parse('/'), array('module' => 'default', 'action' => 'index'), '->parse() Default parameters are applied to serialized routes');
    }
}
