<?php

namespace Symfony1\Components\Log;

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfLoggerWrapper wraps a class that implements sfLoggerInterface into a real sfLogger object.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class LoggerWrapper extends Logger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * Creates a new logger wrapper.
     *
     * @param LoggerInterface $logger The wrapped logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * Logs a message.
     *
     * @param string $message Message
     * @param int $priority Message priority
     */
    protected function doLog($message, $priority)
    {
        $this->logger->log($message, $priority);
    }
}
class_alias(LoggerWrapper::class, 'sfLoggerWrapper', false);