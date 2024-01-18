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

/**
 * @internal
 *
 * @coversNothing
 */
class sfRequestRouteTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->__construct()
        $this->diag('->__construct()');

        $route = new sfRequestRoute('/');
        $requirements = $route->getRequirements();
        $this->is_deeply($requirements['sf_method'], array('get', 'head'), '->__construct() applies a default "sf_method" requirement of GET or HEAD');

        $route = new sfRequestRoute('/', array(), array('sf_method' => array('post')));
        $requirements = $route->getRequirements();
        $this->is_deeply($requirements['sf_method'], array('post'), '->__construct() does not apply a default "sf_method" requirement if one is already set');

        $route = new sfRequestRoute('/', array(), array('sf_method' => 'get'));
        $requirements = $route->getRequirements();
        $this->is_deeply($requirements['sf_method'], array('get'), '->__construct() converts a string "sf_method" requirement to an array');

        // ->matchesParameters()
        $this->diag('->matchesParameters()');

        $route = new sfRequestRoute('/', array(), array('sf_method' => array('get', 'head')));
        $this->ok($route->matchesParameters(array('sf_method' => 'get')), '->matchesParameters() matches the "sf_method" parameter');

        $route = new sfRequestRoute('/', array(), array('sf_method' => array('get')));
        $this->ok($route->matchesParameters(array('sf_method' => 'GET')), '->matchesParameters() checks "sf_method" requirement case-insensitively');

        $route = new sfRequestRoute('/', array(), array('sf_method' => array('GET')));
        $this->ok($route->matchesParameters(array('sf_method' => 'get')), '->matchesParameters() checks "sf_method" requirement case-insensitively');

        // ->matchesUrl()
        $this->diag('->matchesUrl()');

        $route = new sfRequestRoute('/', array(), array('sf_method' => 'GET'));
        $this->isa_ok($route->matchesUrl('/', array('method' => 'get')), 'array', '->matchesUrl() check "sf_method" requirement case-insensitively');

        $route = new sfRequestRoute('/', array(), array('sf_method' => 'get'));
        $this->isa_ok($route->matchesUrl('/', array('method' => 'GET')), 'array', '->matchesUrl() check "sf_method" requirement case-insensitively');
    }
}
