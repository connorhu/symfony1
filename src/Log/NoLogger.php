<?php

namespace Symfony1\Components\Log;

use Symfony1\Components\Event\EventDispatcher;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfNoLogger is a noop logger.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class NoLogger extends Logger
{
    /**
     * Initializes this logger.
     *
     * @param EventDispatcher $dispatcher A sfEventDispatcher instance
     * @param array $options an array of options
     */
    public function initialize(EventDispatcher $dispatcher, $options = array())
    {
    }
    /**
     * Logs a message.
     *
     * @param string $message Message
     * @param int $priority Message priority
     */
    protected function doLog($message, $priority)
    {
    }
}
class_alias(NoLogger::class, 'sfNoLogger', false);