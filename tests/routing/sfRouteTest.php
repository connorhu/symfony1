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
require_once __DIR__.'/../fixtures/CompileCheckRoute.php';
require_once __DIR__.'/../fixtures/MyRoute.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfRouteTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->matchesUrl()
        $this->diag('->matchesUrl()');
        $route = new sfRoute('/');
        $this->is($route->matchesUrl('/'), array(), '->matchesUrl() takes a URL as its first argument');
        $this->is($route->matchesUrl('/foo'), false, '->matchesUrl() returns false if the route does not match');

        $route = new sfRoute('/', array('foo' => 'bar'));
        $this->is($route->matchesUrl('/'), array('foo' => 'bar'), '->matchesUrl() returns default values for parameters not in the route');

        $route = new sfRoute('/:bar', array('foo' => 'bar'));
        $this->is($route->matchesUrl('/foobar'), array('foo' => 'bar', 'bar' => 'foobar'), '->matchesUrl() returns variables from the pattern');

        $route = new sfRoute('/:foo', array('foo' => 'bar'));
        $this->is($route->matchesUrl('/foobar'), array('foo' => 'foobar'), '->matchesUrl() overrides default value with pattern value');

        $route = new sfRoute('/:foo', array('foo' => 'bar'));
        $this->is($route->matchesUrl('/'), array('foo' => 'bar'), '->matchesUrl() matches routes with an optional parameter at the end');

        $route = new sfRoute('/:foo', array('foo' => null));
        $this->is($route->matchesUrl('/'), array('foo' => ''), '->matchesUrl() matches routes with an optional parameter at the end, even if it is null'); // test modified: foo was null

        $route = new sfRoute('/:foo', array('foo' => ''));
        $this->is($route->matchesUrl('/'), array('foo' => ''), '->matchesUrl() matches routes with an optional parameter at the end, even if it is empty');

        $route = new sfRoute('/:foo/bar', array('foo' => null));
        $this->is($route->matchesUrl('//bar'), false, '->matchesUrl() does not match routes with an empty parameter not at the end');
        $this->is($route->matchesUrl('/bar'), false, '->matchesUrl() does not match routes with an empty parameter not at the end');

        $route = new sfRoute('/foo/:foo/bar/:bar', array('foo' => 'bar', 'bar' => 'foo'));
        $this->is($route->matchesUrl('/foo/bar/bar'), array('foo' => 'bar', 'bar' => 'foo'), '->matchesUrl() matches routes with an optional parameter at the end');

        $route = new sfRoute('/:foo/:bar', array('foo' => 'bar', 'bar' => 'foo'));
        $this->is($route->matchesUrl('/'), array('foo' => 'bar', 'bar' => 'foo'), '->matchesUrl() matches routes with multiple optionals parameters at the end');

        $route = new sfRoute('/', array());
        $route->setDefaultParameters(array('foo' => 'bar'));
        $this->is($route->matchesUrl('/'), array('foo' => 'bar'), '->matchesUrl() gets default parameters from the routing object if it exists');

        $route = new sfRoute('/', array('foo' => 'foobar'));
        $route->setDefaultParameters(array('foo' => 'bar'));
        $this->is($route->matchesUrl('/'), array('foo' => 'foobar'), '->matchesUrl() overrides routing default parameters with route default parameters');

        $route = new sfRoute('/:foo', array('foo' => 'foobar'));
        $route->setDefaultParameters(array('foo' => 'bar'));
        $this->is($route->matchesUrl('/barfoo'), array('foo' => 'barfoo'), '->matchesUrl() overrides routing default parameters with pattern parameters');

        $route = new sfRoute('/:foo', array(), array('foo' => '\d+'));
        $this->is($route->matchesUrl('/bar'), false, '->matchesUrl() enforces requirements');

        $route = new sfRoute('/:foo', array(), array('foo' => '\w+'));
        $this->is($route->matchesUrl('/bar'), array('foo' => 'bar'), '->matchesUrl() enforces requirements');

        // ->matchesParameters()
        $this->diag('->matchesParameters()');
        $route = new sfRoute('/', array());
        $this->is($route->matchesParameters('string'), false, '->matchesParameters() returns false if the argument is not an array of parameters');

        $route = new sfRoute('/:foo');
        $this->is($route->matchesParameters(array()), false, '->matchesParameters() returns false if one of the pattern variable is not provided');

        $route = new sfRoute('/:foo', array('foo' => 'bar'));
        $this->is($route->matchesParameters(array()), true, '->matchesParameters() merges the default parameters with the provided parameters to match the route');

        $route = new sfRoute('/:foo');
        $this->is($route->matchesParameters(array('foo' => 'bar')), true, '->matchesParameters() matches if all variables are given as parameters');

        $route = new sfRoute('/:foo');
        $this->is($route->matchesParameters(array('foo' => '')), true, '->matchesParameters() matches if optional parameters empty');
        $this->is($route->matchesParameters(array('foo' => null)), true, '->matchesParameters() matches if optional parameters empty');

        /*
        $route = new sfRoute('/:foo/bar');
        $this->is($route->matchesParameters(array('foo' => '')), false, '->matchesParameters() does not match is required parameters are empty');
        $this->is($route->matchesParameters(array('foo' => null)), false, '->matchesParameters() does not match is required parameters are empty');
        */

        $route = new sfRoute('/:foo');
        $route->setDefaultParameters(array('foo' => 'bar'));
        $this->is($route->matchesParameters(array()), true, '->matchesParameters() merges the routing default parameters with the provided parameters to match the route');

        $route = new sfRoute('/:foo', array(), array('foo' => '\d+'));
        $this->is($route->matchesParameters(array('foo' => 'bar')), false, '->matchesParameters() enforces requirements');

        $route = new sfRoute('/:foo', array(), array('foo' => '\d+'));
        $this->is($route->matchesParameters(array('foo' => 12)), true, '->matchesParameters() enforces requirements');

        $route = new sfRoute('/', array('foo' => 'bar'));
        $this->is($route->matchesParameters(array('foo' => 'foobar')), false, '->matchesParameters() checks that there is no parameter that is not a pattern variable');

        $route = new sfRoute('/', array('foo' => 'bar'));
        $this->is($route->matchesParameters(array('foo' => 'bar')), true, '->matchesParameters() can override a parameter that is not a pattern variable if the value is the same as the default one');

        $route = new sfRoute('/:foo', array('bar' => 'foo'));
        $this->is($route->matchesParameters(array('foo' => 'bar', 'bar' => 'foo')), true, '->matchesParameters() can override a parameter that is not a pattern variable if the value is the same as the default one');

        $route = new sfRoute('/:foo');
        $this->is($route->matchesParameters(array('foo' => 'bar', 'bar' => 'foo')), true, '->generate() matches even if there are extra parameters');

        $route = new sfRoute('/:foo', array(), array(), array('extra_parameters_as_query_string' => false));
        $this->is($route->matchesParameters(array('foo' => 'bar', 'bar' => 'foo')), false, '->generate() does not match if there are extra parameters if extra_parameters_as_query_string is set to false');

        // ->generate()
        $this->diag('->generate()');
        $route = new sfRoute('/:foo');
        $this->is($route->generate(array('foo' => 'bar')), '/bar', '->generate() generates a URL with the given parameters');
        $route = new sfRoute('/:foo/:foobar');
        $this->is($route->generate(array('foo' => 'bar', 'foobar' => 'barfoo')), '/bar/barfoo', '->generate() replaces longer variables first');

        $route = new sfRoute('/:foo');
        $this->is($route->generate(array('foo' => '')), '/', '->generate() generates a route if a variable is empty');
        $this->is($route->generate(array('foo' => null)), '/', '->generate() generates a route if a variable is empty');
        /*
        $route = new sfRoute('/:foo/bar');
        try
        {
          $route->generate(array('foo' => ''));
          $this->fail('->generate() cannot generate a route if a variable is empty and mandatory');
        }
        catch (Exception $e)
        {
          $this->pass('->generate() cannot generate a route if a variable is empty and mandatory');
        }
        try
        {
          $route->generate(array('foo' => null));
          $this->fail('->generate() cannot generate a route if a variable is empty and mandatory');
        }
        catch (Exception $e)
        {
          $this->pass('->generate() cannot generate a route if a variable is empty and mandatory');
        }
        */
        $route = new sfRoute('/:foo');
        $this->is($route->generate(array('foo' => 'bar', 'bar' => 'foo')), '/bar?bar=foo', '->generate() generates extra parameters as a query string');

        $route = new sfRoute('/:foo', array(), array(), array('extra_parameters_as_query_string' => false));
        $this->is($route->generate(array('foo' => 'bar', 'bar' => 'foo')), '/bar', '->generate() ignores extra parameters if extra_parameters_as_query_string is false');

        // checks that explicit 0 values also work - see #5175
        $route = new sfRoute('/:foo', array(), array(), array('extra_parameters_as_query_string' => true));
        $this->is($route->generate(array('foo' => 'bar', 'bar' => '0')), '/bar?bar=0', '->generate() adds extra parameters if extra_parameters_as_query_string is true');

        $route = new sfRoute('/:foo/:bar', array('bar' => 'foo'));
        $this->is($route->generate(array('foo' => 'bar')), '/bar', '->generate() generates the shortest URL possible');

        $route = new sfRoute('/:foo/:bar', array('bar' => 'foo'), array(), array('generate_shortest_url' => false));
        $this->is($route->generate(array('foo' => 'bar')), '/bar/foo', '->generate() generates the longest URL possible if generate_shortest_url is false');

        // ->parseStarParameter()
        $this->diag('->parseStarParameter()');
        $route = new sfRoute('/foo/*');
        $this->is($route->matchesUrl('/foo/foo/bar/bar/foo'), array('foo' => 'bar', 'bar' => 'foo'), '->parseStarParameter() parses * as key/value pairs');
        $this->is($route->matchesUrl('/foo/foo/foo.bar'), array('foo' => 'foo.bar'), '->parseStarParameter() uses / as the key/value separator');
        $this->is($route->matchesUrl('/foo'), array(), '->parseStarParameter() returns no additional parameters if the * value is empty');

        $route = new sfRoute('/foo/*', array('module' => 'foo'));
        $this->is($route->matchesUrl('/foo/foo/bar/module/barbar'), array('module' => 'foo', 'foo' => 'bar'), '->parseStarParameter() cannot override special module/sction values');

        $route = new sfRoute('/foo/*', array('foo' => 'foo'));
        $this->is($route->matchesUrl('/foo/foo/bar'), array('foo' => 'bar'), '->parseStarParameter() can override a default value');

        $route = new sfRoute('/:foo/*');
        $this->is($route->matchesUrl('/bar/foo/barbar'), array('foo' => 'bar'), '->parseStarParameter() cannot override pattern variables');

        $route = new sfRoute('/foo/*/bar');
        $this->is($route->matchesUrl('/foo/foo/bar/bar'), array('foo' => 'bar'), '->parseStarParameter() is able to parse a star in the middle of a rule');
        $this->is($route->matchesUrl('/foo/bar'), array(), '->parseStarParameter() is able to parse a star if it is empty');

        // ->generateStarParameter()
        $this->diag('->generateStarParameter()');
        $route = new sfRoute('/foo/:foo/*');
        $this->is($route->generate(array('foo' => 'bar', 'bar' => 'foo')), '/foo/bar/bar/foo', '->generateStarParameter() replaces * with all the key/pair values that are not variables');

        // custom token
        $this->diag('custom token');

        $route = new MyRoute('/=foo');
        $this->is($route->matchesUrl('/foo/bar'), array('foo' => 'bar'), '->tokenizeBufferBefore() allows to add a custom token');
        $this->is($route->generate(array('foo' => 'bar')), '/foo/bar', '->compileForLabel() adds logic to generate a route for a custom token');

        // state checking
        $this->diag('state checking');
        $route = new CompileCheckRoute('/foo');
        $this->is($route->isCompiled(), false, '__construct() creates an uncompiled instanceof sfRoute');
        $this->is($route->isBound(), false, '->isBound() returns false before binding');
        $route->bind(null, array('foo' => 'bar'));
        $this->is($route->isBound(), true, '->isBound() returns true after binding');
        $this->is($route->getParameters(), array('foo' => 'bar'), '->getParameters() compiles the route and returns parameters');
        $this->is($route->isCompiled(), true, '->getParameters() compiles the route and returns parameters');

        $route = new CompileCheckRoute('/foo');
        $this->is($route->getPattern(), '/foo', '->getPattern() compiles the route and returns the pattern');
        $this->is($route->isCompiled(), true, '->getPattern() compiles the route and returns pattern');

        $route = new CompileCheckRoute('/foo', array('default' => 'bar'));
        $this->is($route->getDefaults(), array('default' => 'bar'), '->getDefaults() compiles the route and returns the defaults');
        $this->is($route->isCompiled(), true, '->getDefaults() compiles the route and returns defaults');

        $route = new CompileCheckRoute('/foo', array(), array('requirements' => 'bar'));
        $this->is($route->getRequirements(), array('requirements' => 'bar'), '->getRequirements() compiles the route and returns the requirements');
        $this->is($route->isCompiled(), true, '->getRequirements() compiles the route and returns requirements');

        $route = new CompileCheckRoute('/foo', array(), array(), array('options' => 'bar'));
        $options = $route->getOptions();
        $this->is($options['options'], 'bar', '->getOptions() compiles the route and returns the compiled options');
        $this->is(count($options) > 1, true, '->getOptions() compiles the route and returns many compiled options');
        $this->is($route->isCompiled(), true, '->getOptions() compiles the route and returns compiled options');
    }
}
