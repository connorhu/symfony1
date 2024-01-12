<?php

namespace Symfony1\Components\Action;

use Symfony1\Components\Util\Context;
use Symfony1\Components\Event\EventDispatcher;
use Symfony1\Components\Request\Request;
use Symfony1\Components\Response\Response;
use Symfony1\Components\Util\ParameterHolder;
use Symfony1\Components\Exception\Exception;
use Symfony1\Components\Event\Event;
use Symfony1\Components\Service\ServiceContainer;
use Symfony1\Components\Log\Logger;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Controller\Controller;
use Symfony1\Components\User\User;
use Symfony1\Components\Mailer\Mailer;
use Symfony1\Components\Escaper\OutputEscaperSafe;
use function sprintf;
use function get_class;
use function constant;
use function strtoupper;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfComponent.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
abstract class Component
{
    /**
     * @var string
     */
    protected $moduleName = '';
    /**
     * @var string
     */
    protected $actionName = '';
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var ParameterHolder
     */
    protected $varHolder;
    /**
     * @var ParameterHolder
     */
    protected $requestParameterHolder;
    /**
     * Class constructor.
     *
     * @see initialize()
     *
     * @param Context $context
     * @param string $moduleName
     * @param string $actionName
     */
    public function __construct($context, $moduleName, $actionName)
    {
        $this->initialize($context, $moduleName, $actionName);
    }
    /**
     * Gets the translation for the given string.
     *
     * @param string $string The string to translate
     * @param array $args An array of arguments for the translation
     * @param string $catalogue The catalogue name
     *
     * @return string The translated string
     */
    public function __($string, $args = array(), $catalogue = 'messages')
    {
        return $this->context->getI18N()->__($string, $args, $catalogue);
    }
    /**
     * Sets a variable for the template.
     *
     * This is a shortcut for:
     *
     * <code>$this->setVar('name', 'value')</code>
     *
     * @param string $key The variable name
     * @param string $value The variable value
     *
     * @return bool always true
     *
     * @see setVar()
     */
    public function __set($key, $value)
    {
        return $this->varHolder->setByRef($key, $value);
    }
    /**
     * Returns true if a variable for the template is set.
     *
     * This is a shortcut for:
     *
     * <code>$this->getVarHolder()->has('name')</code>
     *
     * @param string $name The variable name
     *
     * @return bool true if the variable is set
     */
    public function __isset($name)
    {
        return $this->varHolder->has($name);
    }
    /**
     * Removes a variable for the template.
     *
     * This is just really a shortcut for:
     *
     * <code>$this->getVarHolder()->remove('name')</code>
     *
     * @param string $name The variable Name
     */
    public function __unset($name)
    {
        $this->varHolder->remove($name);
    }
    /**
     * Calls methods defined via sfEventDispatcher.
     *
     * @param string $method The method name
     * @param array $arguments The method arguments
     *
     * @return mixed The returned value of the called method
     *
     * @throws Exception If called method is undefined
     */
    public function __call($method, $arguments)
    {
        $event = $this->dispatcher->notifyUntil(new Event($this, 'component.method_not_found', array('method' => $method, 'arguments' => $arguments)));
        if (!$event->isProcessed()) {
            throw new Exception(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
        }
        return $event->getReturnValue();
    }
    /**
     * Initializes this component.
     *
     * @param Context $context the current application context
     * @param string $moduleName the module name
     * @param string $actionName the action name
     */
    public function initialize($context, $moduleName, $actionName)
    {
        $this->moduleName = $moduleName;
        $this->actionName = $actionName;
        $this->context = $context;
        $this->dispatcher = $context->getEventDispatcher();
        $this->varHolder = new ParameterHolder();
        $this->request = $context->getRequest();
        $this->response = $context->getResponse();
        $this->requestParameterHolder = $this->request->getParameterHolder();
    }
    /**
    * Execute any application/business logic for this component.
    *
    * In a typical database-driven application, execute() handles application
    logic itself and then proceeds to create a model instance. Once the model
    instance is initialized it handles all business logic for the action.
    *
    * A model should represent an entity in your application. This could be a
    user account, a shopping cart, or even a something as simple as a
    single product.
    *
    * @param Request $request The current sfRequest object
    *
    * @return mixed A string containing the view name associated with this action
    */
    public abstract function execute($request);
    /**
     * Gets the module name associated with this component.
     *
     * @return string A module name
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }
    /**
     * Gets the action name associated with this component.
     *
     * @return string An action name
     */
    public function getActionName()
    {
        return $this->actionName;
    }
    /**
     * Retrieves the current application context.
     *
     * @return Context The current sfContext instance
     */
    public final function getContext()
    {
        return $this->context;
    }
    /**
     * Retrieves the current service container instance.
     *
     * @return ServiceContainer The current sfServiceContainer instance
     */
    public final function getServiceContainer()
    {
        return $this->context->getServiceContainer();
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
     * Retrieves the current logger instance.
     *
     * @return Logger The current sfLogger instance
     */
    public final function getLogger()
    {
        return $this->context->getLogger();
    }
    /**
    * Logs a message using the sfLogger object.
    *
    * @param mixed $message String or object containing the message to log
    * @param string $priority The priority of the message
    (available priorities: emerg, alert, crit, err,
    warning, notice, info, debug)
    *
    * @see sfLogger
    */
    public function logMessage($message, $priority = 'info')
    {
        if (Config::get('sf_logging_enabled')) {
            $this->dispatcher->notify(new Event($this, 'application.log', array($message, 'priority' => constant('sfLogger::' . strtoupper($priority)))));
        }
    }
    /**
     * Returns the value of a request parameter.
     *
     * This is a proxy method equivalent to:
     *
     * <code>$this->getRequest()->getParameterHolder()->get($name)</code>
     *
     * @param string $name The parameter name
     * @param mixed $default The default value if parameter does not exist
     *
     * @return string The request parameter value
     */
    public function getRequestParameter($name, $default = null)
    {
        return $this->requestParameterHolder->get($name, $default);
    }
    /**
     * Returns true if a request parameter exists.
     *
     * This is a proxy method equivalent to:
     *
     * <code>$this->getRequest()->getParameterHolder()->has($name)</code>
     *
     * @param string $name The parameter name
     *
     * @return bool true if the request parameter exists, false otherwise
     */
    public function hasRequestParameter($name)
    {
        return $this->requestParameterHolder->has($name);
    }
    /**
     * Retrieves the current sfRequest object.
     *
     * This is a proxy method equivalent to:
     *
     * <code>$this->getContext()->getRequest()</code>
     *
     * @return Request The current sfRequest implementation instance
     */
    public function getRequest()
    {
        return $this->request;
    }
    /**
     * Retrieves the current sfResponse object.
     *
     * This is a proxy method equivalent to:
     *
     * <code>$this->getContext()->getResponse()</code>
     *
     * @return Response The current sfResponse implementation instance
     */
    public function getResponse()
    {
        return $this->response;
    }
    /**
     * Retrieves the current sfController object.
     *
     * This is a proxy method equivalent to:
     *
     * <code>$this->getContext()->getController()</code>
     *
     * @return Controller The current sfController implementation instance
     */
    public function getController()
    {
        return $this->context->getController();
    }
    /**
     * Generates a URL for the given route and arguments.
     *
     * This is a proxy method equivalent to:
     *
     * <code>$this->getContext()->getRouting()->generate(...)</code>
     *
     * @param string $route The route name
     * @param array $params An array of parameters for the route
     * @param bool $absolute Whether to generate an absolute URL or not
     *
     * @return string The URL
     */
    public function generateUrl($route, $params = array(), $absolute = false)
    {
        return $this->context->getRouting()->generate($route, $params, $absolute);
    }
    /**
     * Retrieves the current sfUser object.
     *
     * This is a proxy method equivalent to:
     *
     * <code>$this->getContext()->getUser()</code>
     *
     * @return User The current sfUser implementation instance
     */
    public function getUser()
    {
        return $this->context->getUser();
    }
    /**
     * Gets the current mailer instance.
     *
     * @return Mailer A sfMailer instance
     */
    public function getMailer()
    {
        return $this->getContext()->getMailer();
    }
    /**
    * Sets a variable for the template.
    *
    * If you add a safe value, the variable won't be output escaped
    by symfony, so this is your responsability to ensure that the
    value is escaped properly.
    *
    * @param string $name The variable name
    * @param mixed $value The variable value
    * @param bool $safe true if the value is safe for output (false by default)
    */
    public function setVar($name, $value, $safe = false)
    {
        $this->varHolder->set($name, $safe ? new OutputEscaperSafe($value) : $value);
    }
    /**
     * Gets a variable set for the template.
     *
     * @param string $name The variable name
     *
     * @return mixed The variable value
     */
    public function getVar($name)
    {
        return $this->varHolder->get($name);
    }
    /**
     * Gets the sfParameterHolder object that stores the template variables.
     *
     * @return ParameterHolder the variable holder
     */
    public function getVarHolder()
    {
        return $this->varHolder;
    }
    /**
     * Gets a variable for the template.
     *
     * This is a shortcut for:
     *
     * <code>$this->getVar('name')</code>
     *
     * @param string $key The variable name
     *
     * @return mixed The variable value
     *
     * @see getVar()
     */
    public function &__get($key)
    {
        return $this->varHolder->get($key);
    }
}
class_alias(Component::class, 'sfComponent', false);