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
class sfStreamLoggerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dispatcher = new sfEventDispatcher();

        $buffer = fopen('php://memory', 'rw');
        $logger = new sfStreamLogger($dispatcher, array('stream' => $buffer));

        $logger->log('foo');
        rewind($buffer);
        $this->is(fix_linebreaks(stream_get_contents($buffer)), "foo\n", 'sfStreamLogger logs messages to a PHP stream');
    }
}
