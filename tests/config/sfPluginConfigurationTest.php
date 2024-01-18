<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../lib/test/Symfony1ProjectTestCase.php';
$rootDir = realpath(__DIR__.'/../fixtures/symfony');
$pluginRoot = realpath($rootDir.'/plugins/sfAutoloadPlugin');
require_once $pluginRoot.'/config/sfAutoloadPluginConfiguration.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfPluginConfigurationTest extends Symfony1ProjectTestCase
{
    public function projectSetup(sfProjectConfiguration $configuration)
    {
        $configuration->enablePlugins('sfAutoloadPlugin');
    }

    public function testGuessRootDirAndName()
    {
        $rootDir = realpath(__DIR__.'/../fixtures/symfony');
        $pluginRoot = realpath($rootDir.'/plugins/sfAutoloadPlugin');

        $configuration = $this->getProjectConfiguration();
        $pluginConfig = new sfAutoloadPluginConfiguration($configuration);

        $this->assertSame($pluginRoot, $pluginConfig->getRootDir(), '->guessRootDir() guesses plugin root directory');
        $this->assertSame('sfAutoloadPlugin', $pluginConfig->getName(), '->guessName() guesses plugin name');
    }

    public function testFilterTestFiles()
    {
        $rootDir = realpath(__DIR__.'/../fixtures/symfony');

        $configuration = $this->getProjectConfiguration();
        $pluginConfig = new sfAutoloadPluginConfiguration($configuration);

        $task = new sfTestAllTask($configuration->getEventDispatcher(), new sfFormatter());
        $event = new sfEvent($task, 'task.test.filter_test_files', array('arguments' => array(), 'options' => array()));
        $files = $pluginConfig->filterTestFiles($event, array());
        $this->assertSame(6, \count($files), '->filterTestFiles() adds all plugin tests');
    }

    public function testTestFunctional()
    {
        $rootDir = realpath(__DIR__.'/../fixtures/symfony');

        $configuration = $this->getProjectConfiguration();
        $pluginConfig = new sfAutoloadPluginConfiguration($configuration);

        $task = new sfTestFunctionalTask($configuration->getEventDispatcher(), new sfFormatter());
        $event = new sfEvent($task, 'task.test.filter_test_files', array('arguments' => array('controller' => array()), 'options' => array()));
        $files = $pluginConfig->filterTestFiles($event, array());
        $this->assertSame(3, \count($files), '->filterTestFiles() adds functional plugin tests');

        $task = new sfTestFunctionalTask($configuration->getEventDispatcher(), new sfFormatter());
        $event = new sfEvent($task, 'task.test.filter_test_files', array('arguments' => array('controller' => array('BarFunctional')), 'options' => array()));
        $files = $pluginConfig->filterTestFiles($event, array());
        $this->assertSame(1, \count($files), '->filterTestFiles() adds functional plugin tests when a controller is specified');

        $task = new sfTestFunctionalTask($configuration->getEventDispatcher(), new sfFormatter());
        $event = new sfEvent($task, 'task.test.filter_test_files', array('arguments' => array('controller' => array('nested/NestedFunctional')), 'options' => array()));
        $files = $pluginConfig->filterTestFiles($event, array());
        $this->assertSame(1, \count($files), '->filterTestFiles() adds functional plugin tests when a nested controller is specified');
    }

    public function testTestUnit()
    {
        $rootDir = realpath(__DIR__.'/../fixtures/symfony');

        $configuration = $this->getProjectConfiguration();
        $pluginConfig = new sfAutoloadPluginConfiguration($configuration);

        $task = new sfTestUnitTask($configuration->getEventDispatcher(), new sfFormatter());
        $event = new sfEvent($task, 'task.test.filter_test_files', array('arguments' => array('name' => array()), 'options' => array()));
        $files = $pluginConfig->filterTestFiles($event, array());
        $this->assertSame(3, \count($files), '->filterTestFiles() adds unit plugin tests');

        $task = new sfTestUnitTask($configuration->getEventDispatcher(), new sfFormatter());
        $event = new sfEvent($task, 'task.test.filter_test_files', array('arguments' => array('name' => array('FooUnit')), 'options' => array()));
        $files = $pluginConfig->filterTestFiles($event, array());
        $this->assertSame(1, \count($files), '->filterTestFiles() adds unit plugin tests when a name is specified');

        $task = new sfTestUnitTask($configuration->getEventDispatcher(), new sfFormatter());
        $event = new sfEvent($task, 'task.test.filter_test_files', array('arguments' => array('name' => array('nested/NestedUnit')), 'options' => array()));
        $files = $pluginConfig->filterTestFiles($event, array());
        $this->assertSame(1, \count($files), '->filterTestFiles() adds unit plugin tests when a nested name is specified');
    }
}
