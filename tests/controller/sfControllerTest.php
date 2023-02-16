<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../fixtures/myController.php';
require_once __DIR__.'/../sfEventDispatcherTestCase.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfControllerTest extends sfEventDispatcherTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $context = sfContext::getInstance();
        $this->testObject = new myController($context);
        $this->dispatcher = $context->getEventDispatcher();
        $this->class = 'controller';
    }
}
