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
require_once __DIR__.'/../fixtures/BaseTestTask.php';
require_once __DIR__.'/../fixtures/ArgumentsTest1Task.php';
require_once __DIR__.'/../fixtures/ArgumentsTest2Task.php';
require_once __DIR__.'/../fixtures/OptionsTest1Task.php';
require_once __DIR__.'/../fixtures/DetailedDescriptionTestTask.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfTaskTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->run()
        $this->diag('->run()');

        $task = new ArgumentsTest1Task();
        $task->run(array('FOO'));
        $this->is_deeply($task->lastArguments, array('foo' => 'FOO', 'bar' => null), '->run() accepts an indexed array of arguments');

        $task->run(array('foo' => 'FOO'));
        $this->is_deeply($task->lastArguments, array('foo' => 'FOO', 'bar' => null), '->run() accepts an associative array of arguments');

        $task->run(array('bar' => 'BAR', 'foo' => 'FOO'));
        $this->is_deeply($task->lastArguments, array('foo' => 'FOO', 'bar' => 'BAR'), '->run() accepts an unordered associative array of arguments');

        $task->run('FOO BAR');
        $this->is_deeply($task->lastArguments, array('foo' => 'FOO', 'bar' => 'BAR'), '->run() accepts a string of arguments');

        $task->run(array('foo' => 'FOO', 'bar' => null));
        $this->is_deeply($task->lastArguments, array('foo' => 'FOO', 'bar' => null), '->run() accepts an associative array of arguments when optional arguments are passed as null');

        $task->run(array('bar' => null, 'foo' => 'FOO'));
        $this->is_deeply($task->lastArguments, array('foo' => 'FOO', 'bar' => null), '->run() accepts an unordered associative array of arguments when optional arguments are passed as null');

        $task = new ArgumentsTest2Task();
        $task->run(array('arg1', 'arg2', 'arg3'));
        $this->is_deeply($task->lastArguments, array('foo' => array('arg1', 'arg2', 'arg3')), '->run() accepts an indexed array of an IS_ARRAY argument');

        $task->run(array('foo' => array('arg1', 'arg2', 'arg3')));
        $this->is_deeply($task->lastArguments, array('foo' => array('arg1', 'arg2', 'arg3')), '->run() accepts an associative array of an IS_ARRAY argument');

        $task = new OptionsTest1Task();
        $task->run();
        $this->is_deeply($task->lastOptions, array('none' => false, 'required' => null, 'optional' => null, 'array' => array()), '->run() sets empty option values');

        $task->run(array(), array('--none', '--required=TEST1', '--array=one', '--array=two', '--array=three'));
        $this->is_deeply($task->lastOptions, array('none' => true, 'required' => 'TEST1', 'optional' => null, 'array' => array('one', 'two', 'three')), '->run() accepts an indexed array of option values');

        $task->run(array(), array('none', 'required=TEST1', 'array=one', 'array=two', 'array=three'));
        $this->is_deeply($task->lastOptions, array('none' => true, 'required' => 'TEST1', 'optional' => null, 'array' => array('one', 'two', 'three')), '->run() accepts an indexed array of unflagged option values');

        $task->run(array(), array('none' => false, 'required' => 'TEST1', 'array' => array('one', 'two', 'three')));
        $this->is_deeply($task->lastOptions, array('none' => false, 'required' => 'TEST1', 'optional' => null, 'array' => array('one', 'two', 'three')), '->run() accepts an associative array of option values');

        $task->run(array(), array('optional' => null, 'array' => array()));
        $this->is_deeply($task->lastOptions, array('none' => false, 'required' => null, 'optional' => null, 'array' => array()), '->run() accepts an associative array of options when optional values are passed as empty');

        $task->run('--none --required=TEST1 --array=one --array=two --array=three');
        $this->is_deeply($task->lastOptions, array('none' => true, 'required' => 'TEST1', 'optional' => null, 'array' => array('one', 'two', 'three')), '->run() accepts a string of options');

        $task->run(array(), array('array' => 'one'));
        $this->is_deeply($task->lastOptions, array('none' => false, 'required' => null, 'optional' => null, 'array' => array('one')), '->run() accepts an associative array of options with a scalar array option value');

        // ->getDetailedDescription()
        $this->diag('->getDetailedDescription()');

        $task = new DetailedDescriptionTestTask();
        $this->is($task->getDetailedDescription(), 'The detailedDescription formats special string like ... or --xml', '->getDetailedDescription() formats special string');
    }
}
