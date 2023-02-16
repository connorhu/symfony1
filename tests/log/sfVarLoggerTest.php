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
class sfVarLoggerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dispatcher = new sfEventDispatcher();

        $buffer = fopen('php://memory', 'rw');
        $logger = new sfVarLogger($dispatcher);

        $logger->log('foo');
        $logger->log('{sfFoo} bar', sfLogger::ERR);

        $logs = $logger->getLogs();
        $this->is(count($logs), 2, 'sfVarLogger logs all messages into its instance');

        $this->is($logs[0]['message'], 'foo', 'sfVarLogger returns an array with the message');
        $this->is($logs[0]['priority'], 6, 'sfVarLogger returns an array with the priority');
        $this->is($logs[0]['priority_name'], 'info', 'sfVarLogger returns an array with the priority name');
        $this->is($logs[0]['type'], 'sfOther', 'sfVarLogger returns an array with the type');

        $this->is($logs[1]['message'], 'bar', 'sfVarLogger returns an array with the message');
        $this->is($logs[1]['priority'], 3, 'sfVarLogger returns an array with the priority');
        $this->is($logs[1]['priority_name'], 'err', 'sfVarLogger returns an array with the priority name');
        $this->is($logs[1]['type'], 'sfFoo', 'sfVarLogger returns an array with the type');
    }
}
