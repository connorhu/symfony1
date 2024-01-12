<?php

namespace Symfony1\Components\Task;

use Symfony1\Components\Config\ApplicationConfiguration;
use Symfony1\Components\Command\SymfonyCommandApplication;
use Symfony1\Components\Mailer\Mailer;
use Symfony1\Components\Routing\Routing;
use Symfony1\Components\Service\ServiceContainer;
use Symfony1\Components\Command\CommandApplication;
use LogicException;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Config\RoutingConfigHandler;
use Symfony1\Components\Event\Event;
use Symfony1\Components\Config\FactoryConfigHandler;
use function class_exists;
use function array_merge;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Base class for tasks that depends on a sfCommandApplication object.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 *
 * @property ApplicationConfiguration $configuration
 */
abstract class CommandApplicationTask extends Task
{
    /**
     * @var SymfonyCommandApplication
     */
    protected $commandApplication;
    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var Routing
     */
    private $routing;
    /**
     * @var ServiceContainer
     */
    private $serviceContainer;
    private $factoryConfiguration;
    /**
     * Sets the command application instance for this task.
     *
     * @param CommandApplication $commandApplication A sfCommandApplication instance
     */
    public function setCommandApplication(CommandApplication $commandApplication = null)
    {
        $this->commandApplication = $commandApplication;
    }
    /**
     * @see sfTask
     */
    public function log($messages)
    {
        if (null === $this->commandApplication || $this->commandApplication->isVerbose()) {
            parent::log($messages);
        }
    }
    /**
     * @see sfTask
     *
     * @param (mixed | null) $size
     */
    public function logSection($section, $message, $size = null, $style = 'INFO')
    {
        if (null === $this->commandApplication || $this->commandApplication->isVerbose()) {
            parent::logSection($section, $message, $size, $style);
        }
    }
    /**
     * Retrieves a service from the service container.
     *
     * @param string $id The service identifier
     *
     * @return object The service instance
     */
    public function getService($id)
    {
        return $this->getServiceContainer()->getService($id);
    }
    /**
     * Creates a new task object.
     *
     * @param string $name The name of the task
     *
     * @return Task
     *
     * @throws LogicException If the current task has no command application
     */
    protected function createTask($name)
    {
        if (null === $this->commandApplication) {
            throw new LogicException('Unable to create a task as no command application is associated with this task yet.');
        }
        $task = $this->commandApplication->getTaskToExecute($name);
        if ($task instanceof CommandApplicationTask) {
            $task->setCommandApplication($this->commandApplication);
        }
        return $task;
    }
    /**
     * Executes another task in the context of the current one.
     *
     * @param string $name The name of the task to execute
     * @param array $arguments An array of arguments to pass to the task
     * @param array $options An array of options to pass to the task
     *
     * @return bool The returned value of the task run() method
     *
     * @see createTask()
     */
    protected function runTask($name, $arguments = array(), $options = array())
    {
        return $this->createTask($name)->run($arguments, $options);
    }
    /**
    * Returns a mailer instance.
    *
    * Notice that your task should accept an application option.
    The mailer configuration is read from the current configuration
    instance, which is automatically created according to the current
    --application option.
    *
    * @return Mailer A sfMailer instance
    */
    protected function getMailer()
    {
        if (null === $this->mailer) {
            $this->mailer = $this->initializeMailer();
        }
        return $this->mailer;
    }
    /**
     * Initialize mailer.
     *
     * @return Mailer A sfMailer instance
     */
    protected function initializeMailer()
    {
        if (!class_exists('Swift')) {
            $swift_dir = Config::get('sf_symfony_lib_dir') . '/vendor/swiftmailer/lib';
            require_once $swift_dir . '/swift_required.php';
        }
        $config = $this->getFactoryConfiguration();
        return new $config['mailer']['class']($this->dispatcher, $config['mailer']['param']);
    }
    /**
    * Returns a routing instance.
    *
    * Notice that your task should accept an application option.
    The routing configuration is read from the current configuration
    instance, which is automatically created according to the current
    --application option.
    *
    * @return Routing A sfRouting instance
    */
    protected function getRouting()
    {
        if (null === $this->routing) {
            $this->routing = $this->initializeRouting();
        }
        return $this->routing;
    }
    /**
     * Initialize routing.
     *
     * @return Routing A sfRouting instance
     */
    protected function initializeRouting()
    {
        $config = $this->getFactoryConfiguration();
        $params = array_merge($config['routing']['param'], array('load_configuration' => false, 'logging' => false));
        $handler = new RoutingConfigHandler();
        $routes = $handler->evaluate($this->configuration->getConfigPaths('config/routing.yml'));
        /**
         * @var Routing $routing
         */
        $routing = new $config['routing']['class']($this->dispatcher, null, $params);
        $routing->setRoutes($routes);
        $this->dispatcher->notify(new Event($routing, 'routing.load_configuration'));
        return $routing;
    }
    /**
    * Returns the service container instance.
    *
    * Notice that your task should accept an application option.
    The routing configuration is read from the current configuration
    instance, which is automatically created according to the current
    --application option.
    *
    * @return ServiceContainer An application service container
    */
    protected function getServiceContainer()
    {
        if (null === $this->serviceContainer) {
            $class = (require $this->configuration->getConfigCache()->checkConfig('config/services.yml', true));
            $this->serviceContainer = new $class();
            $this->serviceContainer->setService('sf_event_dispatcher', $this->dispatcher);
            $this->serviceContainer->setService('sf_formatter', $this->formatter);
            $this->serviceContainer->setService('sf_routing', $this->getRouting());
        }
        return $this->serviceContainer;
    }
    /**
     * Gets the factory configuration.
     *
     * @return array
     */
    protected function getFactoryConfiguration()
    {
        if (null === $this->factoryConfiguration) {
            $this->factoryConfiguration = FactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
        }
        return $this->factoryConfiguration;
    }
}
class_alias(CommandApplicationTask::class, 'sfCommandApplicationTask', false);