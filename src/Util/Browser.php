<?php

namespace Symfony1\Components\Util;

use ProjectConfiguration;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Response\WebResponse;
use Symfony1\Components\Request\WebRequest;
use Symfony1\Components\User\User;
use Symfony1\Components\Event\Event;
use function ob_start;
use function ob_get_clean;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfBrowser simulates a browser which can surf a symfony application.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class Browser extends BrowserBase
{
    protected $listeners = array();
    protected $context;
    protected $currentException;
    protected $rawConfiguration = array();
    /**
     * Returns the current application context.
     *
     * @param bool $forceReload true to force context reload, false otherwise
     *
     * @return Context
     */
    public function getContext($forceReload = false)
    {
        if (null === $this->context || $forceReload) {
            $isContextEmpty = null === $this->context;
            $context = $isContextEmpty ? Context::getInstance() : $this->context;
            // create configuration
            $currentConfiguration = $context->getConfiguration();
            $configuration = ProjectConfiguration::getApplicationConfiguration($currentConfiguration->getApplication(), $currentConfiguration->getEnvironment(), $currentConfiguration->isDebug());
            // connect listeners
            $configuration->getEventDispatcher()->connect('application.throw_exception', array($this, 'listenToException'));
            foreach ($this->listeners as $name => $listener) {
                $configuration->getEventDispatcher()->connect($name, $listener);
            }
            // create context
            $this->context = Context::createInstance($configuration);
            unset($currentConfiguration);
            if (!$isContextEmpty) {
                Config::clear();
                Config::add($this->rawConfiguration);
            } else {
                $this->rawConfiguration = Config::getAll();
            }
        }
        return $this->context;
    }
    public function addListener($name, $listener)
    {
        $this->listeners[$name] = $listener;
    }
    /**
     * Gets response.
     *
     * @return WebResponse
     */
    public function getResponse()
    {
        return $this->context->getResponse();
    }
    /**
     * Gets request.
     *
     * @return WebRequest
     */
    public function getRequest()
    {
        return $this->context->getRequest();
    }
    /**
     * Gets user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->context->getUser();
    }
    /**
     * Shutdown function to clean up and remove sessions.
     */
    public function shutdown()
    {
        parent::shutdown();
        // we remove all session data
        Toolkit::clearDirectory(Config::get('sf_test_cache_dir') . '/sessions');
    }
    /**
     * Listener for exceptions.
     *
     * @param Event $event The event to handle
     */
    public function listenToException(Event $event)
    {
        $this->setCurrentException($event->getSubject());
    }
    /**
     * Calls a request to a uri.
     */
    protected function doCall()
    {
        // Before getContext, it can trigger some
        Config::set('sf_test', true);
        // recycle our context object
        $this->context = $this->getContext(true);
        // we register a fake rendering filter
        Config::set('sf_rendering_filter', array('sfFakeRenderingFilter', null));
        $this->resetCurrentException();
        // dispatch our request
        ob_start();
        $this->context->getController()->dispatch();
        $retval = ob_get_clean();
        // append retval to the response content
        $this->context->getResponse()->setContent($retval);
        // manually shutdown user to save current session data
        if ($this->context->getUser()) {
            $this->context->getUser()->shutdown();
            $this->context->getStorage()->shutdown();
        }
    }
}
class_alias(Browser::class, 'sfBrowser', false);