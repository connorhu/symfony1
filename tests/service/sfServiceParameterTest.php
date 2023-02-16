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
class sfServiceParameterTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct() ->__toString()
        $this->diag('__construct() ->__toString()');

        $ref = new sfServiceParameter('foo');
        $this->is((string) $ref, 'foo', '__construct() sets the id of the parameter, which is used for the __toString() method');
    }
}
