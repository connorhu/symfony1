<?php

namespace Symfony1\Components\Exception;

use Symfony1\Components\Config\Config;
use Symfony1\Components\Util\Context;
use Symfony1\Components\Response\WebResponse;
use function error_log;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfError404Exception is thrown when a 404 error occurs in an action.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class Error404Exception extends Exception
{
    /**
     * Forwards to the 404 action.
     */
    public function printStackTrace()
    {
        $exception = null === $this->wrappedException ? $this : $this->wrappedException;
        if (Config::get('sf_debug')) {
            $response = Context::getInstance()->getResponse();
            if (null === $response) {
                $response = new WebResponse(Context::getInstance()->getEventDispatcher());
                Context::getInstance()->setResponse($response);
            }
            $response->setStatusCode(404);
            return parent::printStackTrace();
        }
        // log all exceptions in php log
        if (!Config::get('sf_test')) {
            error_log($this->getMessage());
        }
        Context::getInstance()->getController()->forward(Config::get('sf_error_404_module'), Config::get('sf_error_404_action'));
    }
}
class_alias(Error404Exception::class, 'sfError404Exception', false);