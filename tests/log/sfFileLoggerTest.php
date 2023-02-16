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
require_once __DIR__.'/../fixtures/TestLogger.php';
require_once __DIR__.'/../../lib/util/sfToolkit.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfFileLoggerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sf_log_file.txt';
        if (file_exists($file)) {
            unlink($file);
        }

        $dispatcher = new sfEventDispatcher();

        // ->initialize()
        $this->diag('->initialize()');
        try {
            $logger = new sfFileLogger($dispatcher);
            $this->fail('->initialize() parameters must contains a "file" parameter');
        } catch (sfConfigurationException $e) {
            $this->pass('->initialize() parameters must contains a "file" parameter');
        }

        // ->log()
        $this->diag('->log()');
        $logger = new sfFileLogger($dispatcher, array('file' => $file));
        $logger->log('foo');
        $lines = explode("\n", file_get_contents($file));
        $this->like($lines[0], '/foo/', '->log() logs a message to the file');
        $logger->log('bar');
        $lines = explode("\n", file_get_contents($file));
        $this->like($lines[1], '/bar/', '->log() logs a message to the file');

        // option: format
        $this->diag('option: format');
        unlink($file);
        $logger = new TestLogger($dispatcher, array('file' => $file));
        $logger->log('foo');
        $this->is(file_get_contents($file), TestLogger::strftime($logger->getTimeFormat()).' symfony [*6*] foo'.PHP_EOL, '->initialize() can take a format option');

        unlink($file);
        $logger = new TestLogger($dispatcher, array('file' => $file, 'format' => '%message%'));
        $logger->log('foo');
        $this->is(file_get_contents($file), 'foo', '->initialize() can take a format option');

        // option: time_format
        $this->diag('option: time_format');
        unlink($file);
        $logger = new TestLogger($dispatcher, array('file' => $file, 'time_format' => '%Y %m %d'));
        $logger->log('foo');
        $this->is(file_get_contents($file), TestLogger::strftime($logger->getTimeFormat()).' symfony [*6*] foo'.PHP_EOL, '->initialize() can take a format option');

        // option: type
        $this->diag('option: type');
        unlink($file);
        $logger = new TestLogger($dispatcher, array('file' => $file, 'type' => 'foo'));
        $logger->log('foo');
        $this->is(file_get_contents($file), TestLogger::strftime($logger->getTimeFormat()).' foo [*6*] foo'.PHP_EOL, '->initialize() can take a format option');

        // ->shutdown()
        $this->diag('->shutdown()');
        $logger->shutdown();

        unlink($file);
    }
}
