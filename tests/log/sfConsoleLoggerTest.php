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
// require_once __DIR__.'/../fixtures/FILENAME.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfConsoleLoggerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $logger = new sfConsoleLogger(new sfEventDispatcher());
        $logger->setStream($buffer = fopen('php://memory', 'rw'));

        $logger->log('foo');
        rewind($buffer);
        $this->is(fix_linebreaks(stream_get_contents($buffer)), "foo\n", 'sfConsoleLogger logs messages to the console');
    }
}
