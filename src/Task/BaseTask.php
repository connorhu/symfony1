<?php

namespace Symfony1\Components\Task;

use Symfony1\Components\Config\ProjectConfiguration;
use Symfony1\Components\Exception\Exception;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Command\CommandManager;
use Symfony1\Components\Event\Event;
use Symfony1\Components\Config\ApplicationConfiguration;
use ProjectConfiguration as ProjectConfiguration1;
use Symfony1\Components\Util\Finder;
use Symfony1\Components\Autoload\Autoload;
use Symfony1\Components\Autoload\SimpleAutoload;
use ReflectionClass;
use Symfony1\Components\Plugin\SymfonyPluginManager;
use Symfony1\Components\Plugin\PearEnvironment;
use function file_exists;
use function is_dir;
use function sprintf;
use function getcwd;
use function count;
use function array_merge;
use function implode;
use function array_diff;
use function get_declared_classes;
use function preg_match;
use function time;
use function floor;
use function str_repeat;
use function number_format;
use function round;
use function memory_get_usage;
use function abs;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Base class for all symfony tasks.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
abstract class BaseTask extends CommandApplicationTask
{
    protected $configuration;
    protected $pluginManager;
    protected $statusStartTime;
    protected $filesystem;
    protected $tokens = array();
    /**
     * Sets the current task's configuration.
     */
    public function setConfiguration(ProjectConfiguration $configuration = null)
    {
        $this->configuration = $configuration;
    }
    /**
     * Returns the filesystem instance.
     *
     * @return Filesystem A sfFilesystem instance
     */
    public function getFilesystem()
    {
        if (!isset($this->filesystem)) {
            if ($this->isVerbose()) {
                $this->filesystem = new Filesystem($this->dispatcher, $this->formatter);
            } else {
                $this->filesystem = new Filesystem();
            }
        }
        return $this->filesystem;
    }
    /**
     * Checks if the current directory is a symfony project directory.
     *
     * @return true if the current directory is a symfony project directory, false otherwise
     *
     * @throws Exception
     */
    public function checkProjectExists()
    {
        if (!file_exists('symfony')) {
            throw new Exception('You must be in a symfony project directory.');
        }
    }
    /**
     * Checks if an application exists.
     *
     * @param string $app The application name
     *
     * @return bool true if the application exists, false otherwise
     *
     * @throws Exception
     */
    public function checkAppExists($app)
    {
        if (!is_dir(Config::get('sf_apps_dir') . '/' . $app)) {
            throw new Exception(sprintf('Application "%s" does not exist', $app));
        }
    }
    /**
     * Checks if a module exists.
     *
     * @param string $app The application name
     * @param string $module The module name
     *
     * @return bool true if the module exists, false otherwise
     *
     * @throws Exception
     */
    public function checkModuleExists($app, $module)
    {
        if (!is_dir(Config::get('sf_apps_dir') . '/' . $app . '/modules/' . $module)) {
            throw new Exception(sprintf('Module "%s/%s" does not exist.', $app, $module));
        }
    }
    /**
     * @see sfTask
     */
    protected function doRun(CommandManager $commandManager, $options)
    {
        $event = $this->dispatcher->filter(new Event($this, 'command.filter_options', array('command_manager' => $commandManager)), $options);
        $options = $event->getReturnValue();
        $this->process($commandManager, $options);
        $event = new Event($this, 'command.pre_command', array('arguments' => $commandManager->getArgumentValues(), 'options' => $commandManager->getOptionValues()));
        $this->dispatcher->notifyUntil($event);
        if ($event->isProcessed()) {
            return $event->getReturnValue();
        }
        $this->checkProjectExists();
        $requiresApplication = $commandManager->getArgumentSet()->hasArgument('application') || $commandManager->getOptionSet()->hasOption('application');
        if (null === $this->configuration || $requiresApplication && !$this->configuration instanceof ApplicationConfiguration) {
            $application = $commandManager->getArgumentSet()->hasArgument('application') ? $commandManager->getArgumentValue('application') : ($commandManager->getOptionSet()->hasOption('application') ? $commandManager->getOptionValue('application') : null);
            $env = $commandManager->getOptionSet()->hasOption('env') ? $commandManager->getOptionValue('env') : 'test';
            if (true === $application) {
                $application = $this->getFirstApplication();
                if ($commandManager->getOptionSet()->hasOption('application')) {
                    $commandManager->setOption($commandManager->getOptionSet()->getOption('application'), $application);
                }
            }
            $this->configuration = $this->createConfiguration($application, $env);
        }
        if (!$this->withTrace()) {
            Config::set('sf_logging_enabled', false);
        }
        $ret = $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());
        $this->dispatcher->notify(new Event($this, 'command.post_command'));
        return $ret;
    }
    /**
     * Checks if trace mode is enabled.
     *
     * @return bool
     */
    protected function withTrace()
    {
        if (null !== $this->commandApplication && !$this->commandApplication->withTrace()) {
            return false;
        }
        return true;
    }
    /**
     * Checks if verbose mode is enabled.
     *
     * @return bool
     */
    protected function isVerbose()
    {
        if (null !== $this->commandApplication && !$this->commandApplication->isVerbose()) {
            return false;
        }
        return true;
    }
    /**
     * Checks if debug mode is enabled.
     *
     * @return bool
     */
    protected function isDebug()
    {
        if (null !== $this->commandApplication && !$this->commandApplication->isDebug()) {
            return false;
        }
        return true;
    }
    /**
     * Creates a configuration object.
     *
     * @param string $application The application name
     * @param string $env The environment name
     *
     * @return ProjectConfiguration A sfProjectConfiguration instance
     */
    protected function createConfiguration($application, $env)
    {
        if (null !== $application) {
            $this->checkAppExists($application);
            require_once Config::get('sf_config_dir') . '/ProjectConfiguration.class.php';
            $configuration = ProjectConfiguration1::getApplicationConfiguration($application, $env, $this->isDebug(), null, $this->dispatcher);
        } else {
            if (file_exists(Config::get('sf_config_dir') . '/ProjectConfiguration.class.php')) {
                require_once Config::get('sf_config_dir') . '/ProjectConfiguration.class.php';
                $configuration = new ProjectConfiguration1(null, $this->dispatcher);
            } else {
                $configuration = new ProjectConfiguration(getcwd(), $this->dispatcher);
            }
            if (null !== $env) {
                Config::set('sf_environment', $env);
            }
            $this->initializeAutoload($configuration);
        }
        return $configuration;
    }
    /**
     * Returns the first application in apps.
     *
     * @return string The Application name
     */
    protected function getFirstApplication()
    {
        if (count($dirs = Finder::type('dir')->maxdepth(0)->follow_link()->relative()->in(Config::get('sf_apps_dir')))) {
            return $dirs[0];
        }
        return null;
    }
    /**
    * Reloads all autoloaders.
    *
    * This method should be called whenever a task generates new classes that
    are to be loaded by the symfony autoloader. It clears the autoloader
    cache for all applications and environments and the current execution.
    *
    * @see initializeAutoload()
    */
    protected function reloadAutoload()
    {
        $this->initializeAutoload($this->configuration, true);
    }
    /**
     * Initializes autoloaders.
     *
     * @param ProjectConfiguration $configuration The current project or application configuration
     * @param bool $reload If true, all autoloaders will be reloaded
     */
    protected function initializeAutoload(ProjectConfiguration $configuration, $reload = false)
    {
        // sfAutoload
        if ($reload) {
            $this->logSection('autoload', 'Resetting application autoloaders');
            $finder = Finder::type('file')->name('*autoload.yml.php');
            $this->getFilesystem()->remove($finder->in(Config::get('sf_cache_dir')));
            Autoload::getInstance()->reloadClasses(true);
        }
        // sfSimpleAutoload
        if (!$configuration instanceof ApplicationConfiguration) {
            // plugins
            if ($reload) {
                foreach ($configuration->getPlugins() as $name) {
                    $configuration->getPluginConfiguration($name)->initializeAutoload();
                }
            }
            // project
            $autoload = SimpleAutoload::getInstance(Config::get('sf_cache_dir') . '/project_autoload.cache');
            $autoload->loadConfiguration(Finder::type('file')->name('autoload.yml')->in(array(Config::get('sf_symfony_lib_dir') . '/config/config', Config::get('sf_config_dir'))));
            $autoload->register();
            if ($reload) {
                $this->logSection('autoload', 'Resetting CLI autoloader');
                $autoload->reload();
            }
        }
    }
    /**
     * Mirrors a directory structure inside the created project.
     *
     * @param string $dir The directory to mirror
     * @param Finder $finder A sfFinder instance to use for the mirroring
     */
    protected function installDir($dir, $finder = null)
    {
        if (null === $finder) {
            $finder = Finder::type('any')->discard('.sf');
        }
        $this->getFilesystem()->mirror($dir, Config::get('sf_root_dir'), $finder);
    }
    /**
     * Replaces tokens in files contained in a given directory.
     *
     * If you don't pass a directory, it will replace in the config/ and lib/ directory.
     *
     * You can define global tokens by defining the $this->tokens property.
     *
     * @param array $dirs An array of directory where to do the replacement
     * @param array $tokens An array of tokens to use
     */
    protected function replaceTokens($dirs = array(), $tokens = array())
    {
        if (!$dirs) {
            $dirs = array(Config::get('sf_config_dir'), Config::get('sf_lib_dir'));
        }
        $tokens = array_merge(isset($this->tokens) ? $this->tokens : array(), $tokens);
        $this->getFilesystem()->replaceTokens(Finder::type('file')->prune('vendor')->in($dirs), '##', '##', $tokens);
    }
    /**
     * Reloads tasks.
     *
     * Useful when you install plugins with tasks and if you want to use them with the runTask() method.
     */
    protected function reloadTasks()
    {
        if (null === $this->commandApplication) {
            return;
        }
        $this->configuration = $this->createConfiguration(null, null);
        $this->commandApplication->clearTasks();
        $this->commandApplication->loadTasks($this->configuration);
        $disabledPluginsRegex = sprintf('#^(%s)#', implode('|', array_diff($this->configuration->getAllPluginPaths(), $this->configuration->getPluginPaths())));
        $tasks = array();
        foreach (get_declared_classes() as $class) {
            $r = new ReflectionClass($class);
            if ($r->isSubclassOf('sfTask') && !$r->isAbstract() && !preg_match($disabledPluginsRegex, $r->getFileName())) {
                $tasks[] = new $class($this->dispatcher, $this->formatter);
            }
        }
        $this->commandApplication->registerTasks($tasks);
    }
    /**
     * Enables a plugin in the ProjectConfiguration class.
     *
     * @param string $plugin The name of the plugin
     */
    protected function enablePlugin($plugin)
    {
        SymfonyPluginManager::enablePlugin($plugin, Config::get('sf_config_dir'));
    }
    /**
     * Disables a plugin in the ProjectConfiguration class.
     *
     * @param string $plugin The name of the plugin
     */
    protected function disablePlugin($plugin)
    {
        SymfonyPluginManager::disablePlugin($plugin, Config::get('sf_config_dir'));
    }
    /**
     * Returns a plugin manager instance.
     *
     * @return SymfonyPluginManager A sfSymfonyPluginManager instance
     */
    protected function getPluginManager()
    {
        if (null === $this->pluginManager) {
            $environment = new PearEnvironment($this->dispatcher, array('plugin_dir' => Config::get('sf_plugins_dir'), 'cache_dir' => Config::get('sf_cache_dir') . '/.pear', 'web_dir' => Config::get('sf_web_dir'), 'config_dir' => Config::get('sf_config_dir')));
            $this->pluginManager = new SymfonyPluginManager($this->dispatcher, $environment);
        }
        return $this->pluginManager;
    }
    /**
     * @see sfCommandApplicationTask
     */
    protected function createTask($name)
    {
        $task = parent::createTask($name);
        if ($task instanceof BaseTask) {
            $task->setConfiguration($this->configuration);
        }
        return $task;
    }
    /**
     * Show status of task.
     *
     * @param int $done
     * @param int $total
     * @param int $size
     */
    protected function showStatus($done, $total, $size = 30)
    {
        // if we go over our bound, just ignore it
        if ($done > $total) {
            $this->statusStartTime = null;
            return;
        }
        if (null === $this->statusStartTime) {
            $this->statusStartTime = time();
        }
        $now = time();
        $perc = (float) ($done / $total);
        $bar = floor($perc * $size);
        $statusBar = "\r[";
        $statusBar .= str_repeat('=', $bar);
        if ($bar < $size) {
            $statusBar .= '>';
            $statusBar .= str_repeat(' ', $size - $bar);
        } else {
            $statusBar .= '=';
        }
        $disp = number_format($perc * 100, 0);
        $statusBar .= "] {$disp}% ({$done}/{$total})";
        $rate = $done ? ($now - $this->statusStartTime) / $done : 0;
        $left = $total - $done;
        $eta = round($rate * $left, 2);
        $elapsed = $now - $this->statusStartTime;
        $eta = $this->convertTime($eta);
        $elapsed = $this->convertTime($elapsed);
        $memory = memory_get_usage(true);
        if ($memory > 1024 * 1024 * 1024 * 10) {
            $memory = sprintf('%.2fGB', $memory / 1024 / 1024 / 1024);
        } elseif ($memory > 1024 * 1024 * 10) {
            $memory = sprintf('%.2fMB', $memory / 1024 / 1024);
        } elseif ($memory > 1024 * 10) {
            $memory = sprintf('%.2fkB', $memory / 1024);
        } else {
            $memory = sprintf('%.2fB', $memory);
        }
        $statusBar .= ' [ remaining: ' . $eta . ' | elapsed: ' . $elapsed . ' ] (memory: ' . $memory . ')     ';
        echo $statusBar;
        // when done, send a newline
        if ($done == $total) {
            $this->statusStartTime = null;
            echo "\n";
        }
    }
    /**
     * Convert time into humain format.
     *
     * @param int $time
     *
     * @return string
     */
    private function convertTime($time)
    {
        $string = '';
        if ($time > 3600) {
            $h = (int) abs($time / 3600);
            $time -= $h * 3600;
            $string .= $h . ' h ';
        }
        if ($time > 60) {
            $m = (int) abs($time / 60);
            $time -= $m * 60;
            $string .= $m . ' min ';
        }
        $string .= (int) $time . ' sec';
        return $string;
    }
}
class_alias(BaseTask::class, 'sfBaseTask', false);