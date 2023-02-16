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
require_once __DIR__.'/../fixtures/ProjectDumper.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfServiceContainerDumperTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $builder = new sfServiceContainerBuilder();
        $dumper = new ProjectDumper($builder);
        try {
            $dumper->dump();
            $this->fail('->dump() returns a LogicException if the dump() method has not been overriden by a children class');
        } catch (LogicException $e) {
            $this->pass('->dump() returns a LogicException if the dump() method has not been overriden by a children class');
        }
    }
}
