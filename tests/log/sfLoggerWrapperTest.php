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
require_once __DIR__.'/../fixtures/myLogger2.php';
require_once __DIR__.'/../fixtures/myLoggerWrapper.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfLoggerWrapperTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $myLogger = new myLogger2();

        // __construct()
        $this->diag('__construct()');
        $logger = new myLoggerWrapper($myLogger);
        $this->is($logger->getLogger(), $myLogger, '__construct() takes a logger that implements sfLoggerInterface as its argument');

        // ->log()
        $this->diag('->log()');
        $logger->log('foo');
        $this->is($myLogger->log, 'foo', '->log() logs a message with the wrapped logger');
    }
}
