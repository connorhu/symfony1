<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../fixtures/myDatabase.php';
require_once __DIR__.'/../sfParameterHolderProxyTestCase.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfDatabaseTest extends sfParameterHolderProxyTestCase
{
    public function setUp(): void
    {
        $this->object = new myDatabase();
        $this->object->initialize(sfContext::getInstance());
    }
}
