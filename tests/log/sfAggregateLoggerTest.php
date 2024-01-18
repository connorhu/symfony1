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
require_once __DIR__.'/../../lib/util/sfToolkit.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfAggregateLoggerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dispatcher = new sfEventDispatcher();

        $file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sf_log_file.txt';
        if (file_exists($file)) {
            unlink($file);
        }
        $fileLogger = new sfFileLogger($dispatcher, array('file' => $file));
        $buffer = fopen('php://memory', 'rw');
        $streamLogger = new sfStreamLogger($dispatcher, array('stream' => $buffer));

        // ->initialize()
        $this->diag('->initialize()');
        $logger = new sfAggregateLogger($dispatcher, array('loggers' => $fileLogger));
        $this->is($logger->getLoggers(), array($fileLogger), '->initialize() can take a "loggers" parameter');

        $logger = new sfAggregateLogger($dispatcher, array('loggers' => array($fileLogger, $streamLogger)));
        $this->is($logger->getLoggers(), array($fileLogger, $streamLogger), '->initialize() can take a "loggers" parameter');

        // ->log()
        $this->diag('->log()');
        $logger->log('foo');
        rewind($buffer);
        $content = stream_get_contents($buffer);
        $lines = explode("\n", file_get_contents($file));
        $this->like($lines[0], '/foo/', '->log() logs a message to all loggers');
        $this->is($content, 'foo'.PHP_EOL, '->log() logs a message to all loggers');

        // ->getLoggers() ->addLoggers() ->addLogger()
        $logger = new sfAggregateLogger($dispatcher);
        $logger->addLogger($fileLogger);
        $this->is($logger->getLoggers(), array($fileLogger), '->addLogger() adds a new sfLogger instance');

        $logger = new sfAggregateLogger($dispatcher);
        $logger->addLoggers(array($fileLogger, $streamLogger));
        $this->is($logger->getLoggers(), array($fileLogger, $streamLogger), '->addLoggers() adds an array of sfLogger instances');

        // ->shutdown()
        $this->diag('->shutdown()');
        $logger->shutdown();

        unlink($file);
    }
}
