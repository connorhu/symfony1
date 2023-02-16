<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../PhpUnitSfTestHelperTrait.php';
require_once __DIR__.'/../sfParameterHolderProxyTestCase.php';
require_once __DIR__.'/../fixtures/myRequest3.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfRequestParameterHolderAttributeTest extends sfParameterHolderProxyTestCase
{
    protected function setUp(): void
    {
        $dispatcher = new sfEventDispatcher();

        $this->object = new myRequest3($dispatcher);
        $this->methodName = 'attribute';
    }
}
