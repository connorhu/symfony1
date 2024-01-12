<?php

namespace Symfony1\Components\Command;

use Symfony1\Components\Log\ConsoleLogger;
use Symfony1\Components\Event\EventDispatcher;
use Symfony1\Components\Event\Event;
use function is_object;
use function get_class;
use function is_string;
use function sprintf;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class CommandLogger extends ConsoleLogger
{
    /**
     * Initializes this logger.
     *
     * @param EventDispatcher $dispatcher A sfEventDispatcher instance
     * @param array $options an array of options
     */
    public function initialize(EventDispatcher $dispatcher, $options = array())
    {
        $dispatcher->connect('command.log', array($this, 'listenToLogEvent'));
        return parent::initialize($dispatcher, $options);
    }
    /**
     * Listens to command.log events.
     *
     * @param Event $event An sfEvent instance
     */
    public function listenToLogEvent(Event $event)
    {
        $priority = isset($event['priority']) ? $event['priority'] : self::INFO;
        $prefix = '';
        if ('application.log' == $event->getName()) {
            $subject = $event->getSubject();
            $subject = is_object($subject) ? get_class($subject) : (is_string($subject) ? $subject : 'main');
            $prefix = '>> ' . $subject . ' ';
        }
        foreach ($event->getParameters() as $key => $message) {
            if ('priority' === $key) {
                continue;
            }
            $this->log(sprintf('%s%s', $prefix, $message), $priority);
        }
    }
}
class_alias(CommandLogger::class, 'sfCommandLogger', false);