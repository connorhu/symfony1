<?php

namespace Symfony1\Components\Action;

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfActionStackEntry represents information relating to a single sfAction request during a single HTTP request.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Sean Kerr <sean@code-box.org>
 *
 * @version SVN: $Id$
 */
class ActionStackEntry
{
    protected $actionInstance;
    protected $actionName;
    protected $moduleName;
    protected $presentation;
    /**
     * Class constructor.
     *
     * @param string $moduleName A module name
     * @param string $actionName An action name
     * @param Action $actionInstance An sfAction implementation instance
     */
    public function __construct($moduleName, $actionName, $actionInstance)
    {
        $this->actionName = $actionName;
        $this->actionInstance = $actionInstance;
        $this->moduleName = $moduleName;
    }
    /**
     * Retrieves this entry's action name.
     *
     * @return string An action name
     */
    public function getActionName()
    {
        return $this->actionName;
    }
    /**
     * Retrieves this entry's action instance.
     *
     * @return Action An sfAction implementation instance
     */
    public function getActionInstance()
    {
        return $this->actionInstance;
    }
    /**
     * Retrieves this entry's module name.
     *
     * @return string A module name
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }
    /**
     * Retrieves this entry's rendered view presentation.
     *
     * This will only exist if the view has processed and the render mode is set to sfView::RENDER_VAR.
     *
     * @return string Rendered view presentation
     */
    public function &getPresentation()
    {
        return $this->presentation;
    }
    /**
     * Sets the rendered presentation for this action.
     *
     * @param string $presentation a rendered presentation
     */
    public function setPresentation(&$presentation)
    {
        $this->presentation =& $presentation;
    }
}
class_alias(ActionStackEntry::class, 'sfActionStackEntry', false);