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
require_once __DIR__.'/../fixtures/myLogger.php';
require_once __DIR__.'/../fixtures/notaLogger.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfLoggerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dispatcher = new sfEventDispatcher();
        $logger = new myLogger($dispatcher, array('log_dir_name' => '/tmp'));

        $options = $logger->getOptions();
        $this->is($options['log_dir_name'], '/tmp', '->getOptions() returns the options for the logger instance');

        // ->setLogLevel() ->getLogLevel()
        $this->diag('->setLogLevel() ->getLogLevel()');
        $this->is($logger->getLogLevel(), sfLogger::INFO, '->getLogLevel() gets the current log level');
        $logger->setLogLevel(sfLogger::WARNING);
        $this->is($logger->getLogLevel(), sfLogger::WARNING, '->setLogLevel() sets the log level');
        $logger->setLogLevel('err');
        $this->is($logger->getLogLevel(), sfLogger::ERR, '->setLogLevel() accepts a class constant or a string as its argument');

        // ->initialize()
        $this->diag('->initialize()');
        $logger->initialize($dispatcher, array('level' => sfLogger::ERR));
        $this->is($logger->getLogLevel(), sfLogger::ERR, '->initialize() takes an array of options as its second argument');

        // ::getPriorityName()
        $this->diag('::getPriorityName()');
        $this->is(sfLogger::getPriorityName(sfLogger::INFO), 'info', '::getPriorityName() returns the name of a priority class constant');
        try {
            sfLogger::getPriorityName(100);
            $this->fail('::getPriorityName() throws an sfException if the priority constant does not exist');
        } catch (sfException $e) {
            $this->pass('::getPriorityName() throws an sfException if the priority constant does not exist');
        }

        // ->log()
        $this->diag('->log()');
        $logger->setLogLevel(sfLogger::DEBUG);
        $logger->log('message');
        $this->is($logger->log, 'message', '->log() logs a message');

        // log level
        $this->diag('log levels');
        foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $level) {
            $levelConstant = 'sfLogger::'.strtoupper($level);

            foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $logLevel) {
                $logLevelConstant = 'sfLogger::'.strtoupper($logLevel);
                $logger->setLogLevel(constant($logLevelConstant));

                $logger->log = '';
                $logger->log('foo', constant($levelConstant));

                $this->is($logger->log, constant($logLevelConstant) >= constant($levelConstant) ? 'foo' : '', sprintf('->log() only logs if the level is >= to the defined log level (%s >= %s)', $logLevelConstant, $levelConstant));
            }
        }

        // shortcuts
        $this->diag('log shortcuts');
        foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $level) {
            $levelConstant = 'sfLogger::'.strtoupper($level);

            foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $logLevel) {
                $logger->setLogLevel(constant('sfLogger::'.strtoupper($logLevel)));

                $logger->log = '';
                $logger->log('foo', constant($levelConstant));
                $log1 = $logger->log;

                $logger->log = '';
                $logger->{$level}('foo');
                $log2 = $logger->log;

                $this->is($log1, $log2, sprintf('->%s($msg) is a shortcut for ->log($msg, %s)', $level, $levelConstant));
            }
        }
    }
}
