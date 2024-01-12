<?php

namespace Symfony1\Components\Log;

use Psr\Log\LoggerInterface;
use Symfony1\Components\Event\EventDispatcher;
use Symfony1\Components\Event\Event;
use function compact;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfPsrLoggerAdapter is meant to be able to use a Prs compliant logger in symfony 1.
 *
 * @see https://github.com/php-fig/log
 *
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class PsrLoggerAdapter extends Logger
{
    /**
     * Buffer to keep all the log before the psr logger is registered.
     *
     * @var array
     */
    private $buffer = array();
    /**
     * The logger that will the log will be forward to.
     *
     * @var LoggerInterface
     */
    private $logger;
    /**
     * The service id that will be use as the psr logger.
     *
     * @var string
     */
    private $loggerServiceId = 'logger.psr';
    /**
    * Initializes this logger.
    *
    * Available options:
    *
    * - logger_service_id: The service id to use as the logger. Default: logger.psr
    - auto_connect: If we must connect automatically to the context.load_factories to set the logger. Default: true
    *
    * @param array $options
    */
    public function initialize(EventDispatcher $dispatcher, $options = array())
    {
        if (isset($options['logger_service_id'])) {
            $this->loggerServiceId = $options['logger_service_id'];
        }
        if (!isset($options['auto_connect']) || $options['auto_connect']) {
            $dispatcher->connect('context.load_factories', array($this, 'listenContextLoadFactoriesEvent'));
        }
        parent::initialize($dispatcher, $options);
    }
    /**
     * Listen the context load factories to get the configure service after the service container is available.
     */
    public function listenContextLoadFactoriesEvent(Event $event)
    {
        $context = $event->getSubject();
        // @var $context sfContext
        $this->setLogger($context->getService($this->loggerServiceId));
        $this->dispatcher->disconnect('context.load_factories', array($this, 'listenContextLoadFactoriesEvent'));
    }
    /**
     * Set the logger.
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->flushBuffer();
    }
    /**
     * Flush the current buffer to the register logger.
     */
    public function flushBuffer()
    {
        if (!$this->logger) {
            $this->buffer = array();
            return;
        }
        foreach ($this->buffer as $log) {
            $this->log($log['message'], $log['priority']);
        }
        $this->buffer = array();
    }
    /**
     * Logs a message.
     *
     * @param string $message Message
     * @param int $priority Message priority
     */
    public function doLog($message, $priority)
    {
        if (!$this->logger) {
            $this->buffer[] = compact('message', 'priority');
            return;
        }
        switch ($priority) {
            case Logger::EMERG:
                $this->logger->emergency($message);
                break;
            case Logger::ALERT:
                $this->logger->alert($message);
                break;
            case Logger::CRIT:
                $this->logger->critical($message);
                break;
            case Logger::ERR:
                $this->logger->error($message);
                break;
            case Logger::WARNING:
                $this->logger->warning($message);
                break;
            case Logger::NOTICE:
                $this->logger->notice($message);
                break;
            case Logger::INFO:
                $this->logger->info($message);
                break;
            case Logger::DEBUG:
                $this->logger->debug($message);
                break;
        }
    }
}
class_alias(PsrLoggerAdapter::class, 'sfPsrLoggerAdapter', false);