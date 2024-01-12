<?php

namespace Symfony1\Components\Log;

use Symfony1\Components\Event\EventDispatcher;
use Symfony1\Components\Event\Event;
/**
* sfEventLogger sends log messages to the event dispatcher to be processed
by registered loggers.
*
* @author Jérôme Tamarelle <jtamarelle@groupe-exp.com>
*/
class EventLogger extends Logger
{
    public function initialize(EventDispatcher $dispatcher, $options = array())
    {
        $this->dispatcher = $dispatcher;
        $this->options = $options;
        if (isset($this->options['level'])) {
            $this->setLogLevel($this->options['level']);
        }
        // Use the default "command.log" event if not overriden
        if (!isset($this->options['event_name'])) {
            $this->options['event_name'] = 'command.log';
        }
    }
    protected function doLog($message, $priority)
    {
        $this->dispatcher->notify(new Event($this, $this->options['event_name'], array($message, 'priority' => $priority)));
    }
}
class_alias(EventLogger::class, 'sfEventLogger', false);