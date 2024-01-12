<?php

namespace Symfony1\Components\Controller;

use Symfony1\Components\Filter\Filter;
use Symfony1\Components\Request\WebRequest;
use Symfony1\Components\Exception\Error404Exception;
use Symfony1\Components\Exception\Exception;
use Exception as Exception1;
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
* sfFrontWebController allows you to centralize your entry point in your web
application, but at the same time allow for any module and action combination
to be requested.
*
* @author Fabien Potencier <fabien.potencier@symfony-project.com>
* @author Sean Kerr <sean@code-box.org>
*
* @version SVN: $Id$
*/
class FrontWebController extends WebController
{
    /**
     * Dispatches a request.
     *
     * This will determine which module and action to use by request parameters specified by the user.
     */
    public function dispatch()
    {
        try {
            // reinitialize filters (needed for unit and functional tests)
            Filter::$filterCalled = array();
            // determine our module and action
            /**
             * @var WebRequest $request
             */
            $request = $this->context->getRequest();
            $moduleName = $request->getParameter('module');
            $actionName = $request->getParameter('action');
            if (empty($moduleName) || empty($actionName)) {
                throw new Error404Exception(sprintf('Empty module and/or action after parsing the URL "%s" (%s/%s).', $request->getPathInfo(), $moduleName, $actionName));
            }
            // make the first request
            $this->forward($moduleName, $actionName);
        } catch (Exception $e) {
            $e->printStackTrace();
        } catch (Exception1 $e) {
            Exception::createFromException($e)->printStackTrace();
        }
    }
}
class_alias(FrontWebController::class, 'sfFrontWebController', false);