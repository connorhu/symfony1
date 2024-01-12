<?php

namespace Symfony1\Components\Action;

use Symfony1\Components\Request\Request;
use Symfony1\Components\Exception\InitializationException;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Event\Event;
use function ucfirst;
use function sprintf;
use function is_callable;
use function get_class;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfActions executes all the logic for the current request.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Sean Kerr <sean@code-box.org>
 *
 * @version SVN: $Id$
 */
abstract class Actions extends Action
{
    /**
    * Dispatches to the action defined by the 'action' parameter of the sfRequest object.
    *
    * This method try to execute the executeXXX() method of the current object where XXX is the
    defined action name.
    *
    * @param Request $request The current sfRequest object
    *
    * @return string A string containing the view name associated with this action
    *
    * @throws InitializationException
    *
    * @see sfAction
    */
    public function execute($request)
    {
        // dispatch action
        $actionToRun = 'execute' . ucfirst($this->getActionName());
        if ('execute' === $actionToRun) {
            // no action given
            throw new InitializationException(sprintf('sfAction initialization failed for module "%s". There was no action given.', $this->getModuleName()));
        }
        if (!is_callable(array($this, $actionToRun))) {
            // action not found
            throw new InitializationException(sprintf('sfAction initialization failed for module "%s", action "%s". You must create a "%s" method.', $this->getModuleName(), $this->getActionName(), $actionToRun));
        }
        if (Config::get('sf_logging_enabled')) {
            $this->dispatcher->notify(new Event($this, 'application.log', array(sprintf('Call "%s->%s()"', get_class($this), $actionToRun))));
        }
        // run action
        return $this->{$actionToRun}($request);
    }
}
class_alias(Actions::class, 'sfActions', false);