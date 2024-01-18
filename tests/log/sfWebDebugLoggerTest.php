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

/**
 * @internal
 *
 * @coversNothing
 */
class sfWebDebugLoggerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $context = sfContext::getInstance(array());
        $dispatcher = new sfEventDispatcher();
        $logger = new sfWebDebugLogger($dispatcher);

        // ->handlePhpError()
        $this->diag('->handlePhpError()');

        $error = error_get_last();
        $logger->handlePhpError(E_NOTICE, '%', __FILE__, __LINE__);
        $this->is_deeply(error_get_last(), $error, '->handlePhpError() works when message has a "%" character');
    }
}
