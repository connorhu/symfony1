<?php

namespace Symfony1\Components\Controller;

use Symfony1\Components\Util\Context;
use Symfony1\Components\Event\EventDispatcher;
use Symfony1\Components\View\View;
use Symfony1\Components\Exception\Exception;
use Symfony1\Components\Event\Event;
use Symfony1\Components\Exception\ConfigurationException;
use Symfony1\Components\Exception\ForwardException;
use Symfony1\Components\Exception\Error404Exception;
use Symfony1\Components\Exception\InitializationException;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Filter\FilterChain;
use Symfony1\Components\Action\Action;
use Symfony1\Components\Action\Component;
use Symfony1\Components\Action\ActionStack;
use Exception as Exception1;
use Symfony1\Components\Exception\RenderException;
use Symfony1\Components\Exception\ControllerException;
use function sprintf;
use function get_class;
use function preg_replace;
use function strtolower;
use function is_readable;
use function class_exists;
use function in_array;
use function ucfirst;
use function get_class_methods;
use function array_map;
use function array_keys;
use function implode;
use const PHP_SAPI;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfController directs application flow.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Sean Kerr <sean@code-box.org>
 *
 * @version SVN: $Id$
 */
abstract class Controller
{
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;
    /**
     * @var string[]
     */
    protected $controllerClasses = array();
    /**
     * @var int
     */
    protected $renderMode = View::RENDER_CLIENT;
    /**
     * @var int
     */
    protected $maxForwards = 5;
    /**
     * Class constructor.
     *
     * @see initialize()
     *
     * @param Context $context A sfContext implementation instance
     */
    public function __construct($context)
    {
        $this->initialize($context);
    }
    /**
     * Calls methods defined via sfEventDispatcher.
     *
     * @param string $method The method name
     * @param array $arguments The method arguments
     *
     * @return mixed The returned value of the called method
     *
     * @throws Exception
     */
    public function __call($method, $arguments)
    {
        $event = $this->dispatcher->notifyUntil(new Event($this, 'controller.method_not_found', array('method' => $method, 'arguments' => $arguments)));
        if (!$event->isProcessed()) {
            throw new Exception(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
        }
        return $event->getReturnValue();
    }
    /**
     * Initializes this controller.
     *
     * @param Context $context A sfContext implementation instance
     */
    public function initialize($context)
    {
        $this->context = $context;
        $this->dispatcher = $context->getEventDispatcher();
    }
    /**
     * Indicates whether or not a module has a specific component.
     *
     * @param string $moduleName A module name
     * @param string $componentName An component name
     *
     * @return bool true, if the component exists, otherwise false
     */
    public function componentExists($moduleName, $componentName)
    {
        return $this->controllerExists($moduleName, $componentName, 'component', false);
    }
    /**
     * Indicates whether or not a module has a specific action.
     *
     * @param string $moduleName A module name
     * @param string $actionName An action name
     *
     * @return bool true, if the action exists, otherwise false
     */
    public function actionExists($moduleName, $actionName)
    {
        return $this->controllerExists($moduleName, $actionName, 'action', false);
    }
    /**
     * Forwards the request to another action.
     *
     * @param string $moduleName A module name
     * @param string $actionName An action name
     *
     * @throws ConfigurationException If an invalid configuration setting has been found
     * @throws ForwardException If an error occurs while forwarding the request
     * @throws Error404Exception If the action not exist
     * @throws InitializationException If the action could not be initialized
     */
    public function forward($moduleName, $actionName)
    {
        // replace unwanted characters
        $moduleName = preg_replace('/[^a-z0-9_]+/i', '', $moduleName);
        $actionName = preg_replace('/[^a-z0-9_]+/i', '', $actionName);
        if ($this->getActionStack()->getSize() >= $this->maxForwards) {
            // let's kill this party before it turns into cpu cycle hell
            throw new ForwardException('Too many forwards have been detected for this request.');
        }
        // check for a module generator config file
        $this->context->getConfigCache()->import('modules/' . $moduleName . '/config/generator.yml', false, true);
        if (!$this->actionExists($moduleName, $actionName)) {
            // the requested action doesn't exist
            if (Config::get('sf_logging_enabled')) {
                $this->dispatcher->notify(new Event($this, 'application.log', array(sprintf('Action "%s/%s" does not exist', $moduleName, $actionName))));
            }
            throw new Error404Exception(sprintf('Action "%s/%s" does not exist.', $moduleName, $actionName));
        }
        // create an instance of the action
        $actionInstance = $this->getAction($moduleName, $actionName);
        // add a new action stack entry
        $this->getActionStack()->addEntry($moduleName, $actionName, $actionInstance);
        // include module configuration
        $viewClass = Config::get('mod_' . strtolower($moduleName) . '_view_class', false);
        require $this->context->getConfigCache()->checkConfig('modules/' . $moduleName . '/config/module.yml');
        if (false !== $viewClass) {
            Config::set('mod_' . strtolower($moduleName) . '_view_class', $viewClass);
        }
        // module enabled?
        if (Config::get('mod_' . strtolower($moduleName) . '_enabled')) {
            // check for a module config.php
            $moduleConfig = Config::get('sf_app_module_dir') . '/' . $moduleName . '/config/config.php';
            if (is_readable($moduleConfig)) {
                require_once $moduleConfig;
            }
            // create a new filter chain
            $filterChain = new FilterChain();
            $filterChain->loadConfiguration($actionInstance);
            $this->context->getEventDispatcher()->notify(new Event($this, 'controller.change_action', array('module' => $moduleName, 'action' => $actionName)));
            if ($moduleName == Config::get('sf_error_404_module') && $actionName == Config::get('sf_error_404_action')) {
                $this->context->getResponse()->setStatusCode(404);
                $this->context->getResponse()->setHttpHeader('Status', '404 Not Found');
                $this->dispatcher->notify(new Event($this, 'controller.page_not_found', array('module' => $moduleName, 'action' => $actionName)));
            }
            // process the filter chain
            $filterChain->execute();
        } else {
            $moduleName = Config::get('sf_module_disabled_module');
            $actionName = Config::get('sf_module_disabled_action');
            if (!$this->actionExists($moduleName, $actionName)) {
                // cannot find mod disabled module/action
                throw new ConfigurationException(sprintf('Invalid configuration settings: [sf_module_disabled_module] "%s", [sf_module_disabled_action] "%s".', $moduleName, $actionName));
            }
            $this->forward($moduleName, $actionName);
        }
    }
    /**
     * Retrieves an sfAction implementation instance.
     *
     * @param string $moduleName A module name
     * @param string $actionName An action name
     *
     * @return Action An sfAction implementation instance, if the action exists, otherwise null
     */
    public function getAction($moduleName, $actionName)
    {
        return $this->getController($moduleName, $actionName, 'action');
    }
    /**
     * Retrieves a sfComponent implementation instance.
     *
     * @param string $moduleName A module name
     * @param string $componentName A component name
     *
     * @return Component A sfComponent implementation instance, if the component exists, otherwise null
     */
    public function getComponent($moduleName, $componentName)
    {
        return $this->getController($moduleName, $componentName, 'component');
    }
    /**
     * Retrieves the action stack.
     *
     * @return ActionStack An sfActionStack instance, if the action stack is enabled, otherwise null
     */
    public function getActionStack()
    {
        return $this->context->getActionStack();
    }
    /**
    * Retrieves the presentation rendering mode.
    *
    * @return int One of the following:
    - sfView::RENDER_CLIENT
    - sfView::RENDER_VAR
    */
    public function getRenderMode()
    {
        return $this->renderMode;
    }
    /**
     * Retrieves a sfView implementation instance.
     *
     * @param string $moduleName A module name
     * @param string $actionName An action name
     * @param string $viewName A view name
     *
     * @return View A sfView implementation instance, if the view exists, otherwise null
     */
    public function getView($moduleName, $actionName, $viewName)
    {
        // user view exists?
        $file = Config::get('sf_app_module_dir') . '/' . $moduleName . '/view/' . $actionName . $viewName . 'View.class.php';
        if (is_readable($file)) {
            require_once $file;
            $class = $actionName . $viewName . 'View';
            // fix for same name classes
            $moduleClass = $moduleName . '_' . $class;
            if (class_exists($moduleClass, false)) {
                $class = $moduleClass;
            }
        } else {
            // view class (as configured in module.yml or defined in action)
            $class = Config::get('mod_' . strtolower($moduleName) . '_view_class', 'sfPHP') . 'View';
        }
        return new $class($this->context, $moduleName, $actionName, $viewName);
    }
    /**
     * Returns the rendered view presentation of a given module/action.
     *
     * @param string $module A module name
     * @param string $action An action name
     * @param string $viewName A View class name
     *
     * @return string The generated content
     *
     * @throws Exception1
     * @throws Exception
     */
    public function getPresentationFor($module, $action, $viewName = null)
    {
        if (Config::get('sf_logging_enabled')) {
            $this->dispatcher->notify(new Event($this, 'application.log', array(sprintf('Get presentation for action "%s/%s" (view class: "%s")', $module, $action, $viewName))));
        }
        // get original render mode
        $renderMode = $this->getRenderMode();
        // set render mode to var
        $this->setRenderMode(View::RENDER_VAR);
        // grab the action stack
        $actionStack = $this->getActionStack();
        // grab this next forward's action stack index
        $index = $actionStack->getSize();
        // set viewName if needed
        if ($viewName) {
            $currentViewName = Config::get('mod_' . strtolower($module) . '_view_class');
            Config::set('mod_' . strtolower($module) . '_view_class', $viewName);
        }
        try {
            // forward to the action
            $this->forward($module, $action);
        } catch (Exception1 $e) {
            // put render mode back
            $this->setRenderMode($renderMode);
            // remove viewName
            if ($viewName) {
                Config::set('mod_' . strtolower($module) . '_view_class', $currentViewName);
            }
            throw $e;
        }
        // grab the action entry from this forward
        $actionEntry = $actionStack->getEntry($index);
        // get raw content
        $presentation =& $actionEntry->getPresentation();
        // put render mode back
        $this->setRenderMode($renderMode);
        // remove the action entry
        $nb = $actionStack->getSize() - $index;
        while ($nb-- > 0) {
            $actionEntry = $actionStack->popEntry();
            if ($actionEntry->getModuleName() == Config::get('sf_login_module') && $actionEntry->getActionName() == Config::get('sf_login_action')) {
                throw new Exception('Your action is secured, but the user is not authenticated.');
            }
            if ($actionEntry->getModuleName() == Config::get('sf_secure_module') && $actionEntry->getActionName() == Config::get('sf_secure_action')) {
                throw new Exception('Your action is secured, but the user does not have access.');
            }
        }
        // remove viewName
        if ($viewName) {
            Config::set('mod_' . strtolower($module) . '_view_class', $currentViewName);
        }
        return $presentation;
    }
    /**
    * Sets the presentation rendering mode.
    *
    * @param int $mode A rendering mode one of the following:
    - sfView::RENDER_CLIENT
    - sfView::RENDER_VAR
    - sfView::RENDER_NONE
    *
    * @throws RenderException If an invalid render mode has been set
    */
    public function setRenderMode($mode)
    {
        if (View::RENDER_CLIENT == $mode || View::RENDER_VAR == $mode || View::RENDER_NONE == $mode) {
            $this->renderMode = $mode;
            return;
        }
        // invalid rendering mode type
        throw new RenderException(sprintf('Invalid rendering mode: %s.', $mode));
    }
    /**
     * Indicates whether or not we were called using the CLI version of PHP.
     *
     * @return bool true, if using cli, otherwise false
     */
    public function inCLI()
    {
        return 'cli' == PHP_SAPI;
    }
    /**
    * Looks for a controller and optionally throw exceptions if existence is required (i.e.
    in the case of {@link getController()}).
    *
    * @param string $moduleName The name of the module
    * @param string $controllerName The name of the controller within the module
    * @param string $extension Either 'action' or 'component' depending on the type of controller to look for
    * @param bool $throwExceptions Whether to throw exceptions if the controller doesn't exist
    *
    * @return bool true if the controller exists, false otherwise
    *
    * @throws ConfigurationException thrown if the module is not enabled
    * @throws ControllerException thrown if the controller doesn't exist and the $throwExceptions parameter is set to true
    */
    protected function controllerExists($moduleName, $controllerName, $extension, $throwExceptions)
    {
        $dirs = $this->context->getConfiguration()->getControllerDirs($moduleName);
        foreach ($dirs as $dir => $checkEnabled) {
            // plugin module enabled?
            if ($checkEnabled && !in_array($moduleName, Config::get('sf_enabled_modules')) && is_readable($dir)) {
                throw new ConfigurationException(sprintf('The module "%s" is not enabled.', $moduleName));
            }
            // check for a module generator config file
            $this->context->getConfigCache()->import('modules/' . $moduleName . '/config/generator.yml', false, true);
            // one action per file or one file for all actions
            $classFile = strtolower($extension);
            $classSuffix = ucfirst(strtolower($extension));
            $file = $dir . '/' . $controllerName . $classSuffix . '.class.php';
            if (is_readable($file)) {
                // action class exists
                require_once $file;
                $this->controllerClasses[$moduleName . '_' . $controllerName . '_' . $classSuffix] = $controllerName . $classSuffix;
                return true;
            }
            $module_file = $dir . '/' . $classFile . 's.class.php';
            if (is_readable($module_file)) {
                // module class exists
                require_once $module_file;
                if (!class_exists($moduleName . $classSuffix . 's', false)) {
                    if ($throwExceptions) {
                        throw new ControllerException(sprintf('There is no "%s" class in your action file "%s".', $moduleName . $classSuffix . 's', $module_file));
                    }
                    return false;
                }
                // action is defined in this class?
                if (!in_array('execute' . ucfirst($controllerName), get_class_methods($moduleName . $classSuffix . 's'))) {
                    if ($throwExceptions) {
                        throw new ControllerException(sprintf('There is no "%s" method in your action class "%s".', 'execute' . ucfirst($controllerName), $moduleName . $classSuffix . 's'));
                    }
                    return false;
                }
                $this->controllerClasses[$moduleName . '_' . $controllerName . '_' . $classSuffix] = $moduleName . $classSuffix . 's';
                return true;
            }
        }
        // send an exception if debug
        if ($throwExceptions && Config::get('sf_debug')) {
            $dirs = array_map(array('sfDebug', 'shortenFilePath'), array_keys($dirs));
            throw new ControllerException(sprintf('Controller "%s/%s" does not exist in: %s.', $moduleName, $controllerName, implode(', ', $dirs)));
        }
        return false;
    }
    /**
     * Retrieves a controller implementation instance.
     *
     * @param string $moduleName A module name
     * @param string $controllerName A component name
     * @param string $extension Either 'action' or 'component' depending on the type of controller to look for
     *
     * @return Action A controller implementation instance, if the controller exists, otherwise null
     *
     * @see getComponent(), getAction()
     */
    protected function getController($moduleName, $controllerName, $extension)
    {
        $classSuffix = ucfirst(strtolower($extension));
        if (!isset($this->controllerClasses[$moduleName . '_' . $controllerName . '_' . $classSuffix])) {
            if (!$this->controllerExists($moduleName, $controllerName, $extension, true)) {
                return null;
            }
        }
        $class = $this->controllerClasses[$moduleName . '_' . $controllerName . '_' . $classSuffix];
        // fix for same name classes
        $moduleClass = $moduleName . '_' . $class;
        if (class_exists($moduleClass, false)) {
            $class = $moduleClass;
        }
        return new $class($this->context, $moduleName, $controllerName);
    }
}
class_alias(Controller::class, 'sfController', false);