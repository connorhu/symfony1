<?php

namespace Symfony1\Components\Filter;

use Symfony1\Components\Action\Action;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Debug\TimerManager;
use Symfony1\Components\View\View;
use function sprintf;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
* sfExecutionFilter is the last filter registered for each filter chain. This
filter does all action and view execution.
*
* @author Fabien Potencier <fabien.potencier@symfony-project.com>
* @author Sean Kerr <sean@code-box.org>
*
* @version SVN: $Id$
*/
class ExecutionFilter extends Filter
{
    /**
     * Executes this filter.
     *
     * @param FilterChain $filterChain The filter chain
     *
     * @throws <b>sfInitializeException</b> If an error occurs during view initialization
     * @throws <b>sfViewException</b>       If an error occurs while executing the view
     */
    public function execute($filterChain)
    {
        // get the current action instance
        /**
         * @var Action $actionInstance
         */
        $actionInstance = $this->context->getController()->getActionStack()->getLastEntry()->getActionInstance();
        // execute the action, execute and render the view
        if (Config::get('sf_debug') && Config::get('sf_logging_enabled')) {
            $timer = TimerManager::getTimer(sprintf('Action "%s/%s"', $actionInstance->getModuleName(), $actionInstance->getActionName()));
            $viewName = $this->handleAction($filterChain, $actionInstance);
            $timer->addTime();
            $timer = TimerManager::getTimer(sprintf('View "%s" for "%s/%s"', $viewName, $actionInstance->getModuleName(), $actionInstance->getActionName()));
            $this->handleView($filterChain, $actionInstance, $viewName);
            $timer->addTime();
        } else {
            $viewName = $this->handleAction($filterChain, $actionInstance);
            $this->handleView($filterChain, $actionInstance, $viewName);
        }
    }
    /**
     * Handles the action.
     *
     * @param FilterChain $filterChain The current filter chain
     * @param Action $actionInstance An sfAction instance
     *
     * @return string The view type
     */
    protected function handleAction($filterChain, $actionInstance)
    {
        if (Config::get('sf_cache')) {
            $uri = $this->context->getViewCacheManager()->getCurrentCacheKey();
            if (null !== $uri && $this->context->getViewCacheManager()->hasActionCache($uri)) {
                // action in cache, so go to the view
                return View::SUCCESS;
            }
        }
        return $this->executeAction($actionInstance);
    }
    /**
     * Executes the execute method of an action.
     *
     * @param Action $actionInstance An sfAction instance
     *
     * @return string The view type
     */
    protected function executeAction($actionInstance)
    {
        // execute the action
        $actionInstance->preExecute();
        $viewName = $actionInstance->execute($this->context->getRequest());
        $actionInstance->postExecute();
        return null === $viewName ? View::SUCCESS : $viewName;
    }
    /**
     * Handles the view.
     *
     * @param FilterChain $filterChain The current filter chain
     * @param Action $actionInstance An sfAction instance
     * @param string $viewName The view name
     */
    protected function handleView($filterChain, $actionInstance, $viewName)
    {
        switch ($viewName) {
            case View::HEADER_ONLY:
                $this->context->getResponse()->setHeaderOnly(true);
                return;
            case View::NONE:
                return;
        }
        $this->executeView($actionInstance->getModuleName(), $actionInstance->getActionName(), $viewName, $actionInstance->getVarHolder()->getAll());
    }
    /**
    * Executes and renders the view.
    *
    * The behavior of this method depends on the controller render mode:
    *
    * - sfView::NONE: Nothing happens.
    - sfView::RENDER_CLIENT: View data populates the response content.
    - sfView::RENDER_VAR: View data populates the data presentation variable.
    *
    * @param string $moduleName The module name
    * @param string $actionName The action name
    * @param string $viewName The view name
    * @param array $viewAttributes An array of view attributes
    *
    * @return string The view data
    */
    protected function executeView($moduleName, $actionName, $viewName, $viewAttributes)
    {
        $controller = $this->context->getController();
        // get the view instance
        $view = $controller->getView($moduleName, $actionName, $viewName);
        // execute the view
        $view->execute();
        // pass attributes to the view
        $view->getAttributeHolder()->add($viewAttributes);
        // render the view
        switch ($controller->getRenderMode()) {
            case View::RENDER_NONE:
                break;
            case View::RENDER_CLIENT:
                $viewData = $view->render();
                $this->context->getResponse()->setContent($viewData);
                break;
            case View::RENDER_VAR:
                $viewData = $view->render();
                $controller->getActionStack()->getLastEntry()->setPresentation($viewData);
                break;
        }
    }
}
class_alias(ExecutionFilter::class, 'sfExecutionFilter', false);