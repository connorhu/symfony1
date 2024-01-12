<?php

namespace Symfony1\Components\Mailer;

use Symfony1\Components\Event\EventDispatcher;
use Swift_Transport;
/**
 * sfMailer is the main entry point for the mailer system - sfNoMailer disable all mailer features.
 */
class NoMailer
{
    public function __construct(EventDispatcher $dispatcher, $options)
    {
    }
    public function getRealtimeTransport()
    {
        return null;
    }
    public function setRealtimeTransport(Swift_Transport $transport)
    {
    }
    public function getLogger()
    {
        return null;
    }
    public function setLogger($logger)
    {
    }
    public function getDeliveryStrategy()
    {
        return null;
    }
    public function getDeliveryAddress()
    {
        return null;
    }
    public function setDeliveryAddress($address)
    {
    }
    public function compose($from = null, $to = null, $subject = null, $body = null)
    {
        return null;
    }
    public function composeAndSend($from, $to, $subject, $body)
    {
        return null;
    }
    public function sendNextImmediately()
    {
        return null;
    }
    public function send($message, &$failedRecipients = null)
    {
        return null;
    }
    public function flushQueue(&$failedRecipients = null)
    {
        return null;
    }
    public function getSpool()
    {
        return null;
    }
}
class_alias(NoMailer::class, 'sfNoMailer', false);