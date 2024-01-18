<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class sfPDOSessionStorageTest extends TestCase
{
    use sfInternalServerBasedFixtureTestTrait;

    protected static $fixtureDirectory = __DIR__.'/../fixtures/storage';
}
