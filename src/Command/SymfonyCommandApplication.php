<?php

namespace Symfony1\Components\Command;

use Symfony1\Components\Exception\InitializationException;
use ProjectConfiguration;
use Symfony1\Components\Config\ProjectConfiguration as ConfigProjectConfiguration;
use Symfony1\Components\Task\CommandApplicationTask;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Util\Finder;
use function getcwd;
use function is_readable;
use function is_dir;
use function basename;
use function spl_autoload_register;
use function class_exists;
use function spl_autoload_unregister;
use function sprintf;
use const SYMFONY_VERSION;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfSymfonyCommandApplication manages the symfony CLI.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class SymfonyCommandApplication extends CommandApplication
{
    protected $taskFiles = array();
    /**
     * Configures the current symfony command application.
     */
    public function configure()
    {
        if (!isset($this->options['symfony_lib_dir'])) {
            throw new InitializationException('You must pass a "symfony_lib_dir" option.');
        }
        $configurationFile = getcwd() . '/config/ProjectConfiguration.class.php';
        if (is_readable($configurationFile)) {
            require_once $configurationFile;
            $configuration = new ProjectConfiguration(getcwd(), $this->dispatcher);
        } else {
            $configuration = new ConfigProjectConfiguration(getcwd(), $this->dispatcher);
        }
        // application
        $this->setName('symfony');
        $this->setVersion(SYMFONY_VERSION);
        $this->loadTasks($configuration);
    }
    /**
     * Runs the current application.
     *
     * @param mixed $options The command line options
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function run($options = null)
    {
        $this->handleOptions($options);
        $arguments = $this->commandManager->getArgumentValues();
        if (!isset($arguments['task'])) {
            $arguments['task'] = 'list';
            $this->commandOptions .= $arguments['task'];
        }
        $this->currentTask = $this->getTaskToExecute($arguments['task']);
        if ($this->currentTask instanceof CommandApplicationTask) {
            $this->currentTask->setCommandApplication($this);
        }
        $ret = $this->currentTask->runFromCLI($this->commandManager, $this->commandOptions);
        $this->currentTask = null;
        return $ret;
    }
    /**
     * Loads all available tasks.
     *
     * Looks for tasks in the symfony core, the current project and all project plugins.
     *
     * @param ConfigProjectConfiguration $configuration The project configuration
     */
    public function loadTasks(ConfigProjectConfiguration $configuration)
    {
        // Symfony core tasks
        $dirs = array(Config::get('sf_symfony_lib_dir') . '/task');
        // Plugin tasks
        foreach ($configuration->getPluginPaths() as $path) {
            if (is_dir($taskPath = $path . '/lib/task')) {
                $dirs[] = $taskPath;
            }
        }
        // project tasks
        $dirs[] = Config::get('sf_lib_dir') . '/task';
        $finder = Finder::type('file')->name('*Task.class.php');
        foreach ($finder->in($dirs) as $file) {
            $this->taskFiles[basename($file, '.class.php')] = $file;
        }
        // register local autoloader for tasks
        spl_autoload_register(array($this, 'autoloadTask'));
        // require tasks
        foreach ($this->taskFiles as $task => $file) {
            // forces autoloading of each task class
            class_exists($task, true);
        }
        // unregister local autoloader
        spl_autoload_unregister(array($this, 'autoloadTask'));
    }
    /**
     * Autoloads a task class.
     *
     * @param string $class The task class name
     *
     * @return bool
     */
    public function autoloadTask($class)
    {
        if (isset($this->taskFiles[$class])) {
            require_once $this->taskFiles[$class];
            return true;
        }
        return false;
    }
    /**
     * @see sfCommandApplication
     */
    public function getLongVersion()
    {
        return sprintf('%s version %s (%s)', $this->getName(), $this->formatter->format($this->getVersion(), 'INFO'), Config::get('sf_symfony_lib_dir')) . "\n";
    }
}
class_alias(SymfonyCommandApplication::class, 'sfSymfonyCommandApplication', false);