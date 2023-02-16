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
require_once __DIR__.'/../sfParameterHolderProxyTestCase.php';
require_once __DIR__.'/../fixtures/myRequest5.php';
require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../fixtures/myView.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfViewParameterHolderEscapingTest extends TestCase
{
    use sfInternalServerBasedFixtureTestTrait;

    protected static $fixtureDirectory = __DIR__.'/../fixtures/view';

//        // ->toArray()
//        $this->diag('->toArray()');
//        $p->initialize($dispatcher, array('foo' => 'bar'));
//        $a = $p->toArray();
//        $this->is($a['foo'], 'bar', '->toArray() returns an array representation of the parameter holder');
}
