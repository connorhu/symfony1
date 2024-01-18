<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../PhpUnitSfTestHelperTrait.php';
require_once sfConfig::get('sf_symfony_lib_dir').'/vendor/swiftmailer/lib/swift_required.php';
require_once __DIR__.'/../fixtures/mailer/TestMailerTransport.class.php';
require_once __DIR__.'/../fixtures/mailer/TestSpool.class.php';
require_once __DIR__.'/../fixtures/mailer/TestMailMessage.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfMailerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dispatcher = new sfEventDispatcher();

        // __construct()
        $this->diag('__construct()');
        try {
            new sfMailer($dispatcher, array('delivery_strategy' => 'foo'));

            $this->fail('__construct() throws an InvalidArgumentException exception if the strategy is not valid');
        } catch (InvalidArgumentException $e) {
            $this->pass('__construct() throws an InvalidArgumentException exception if the strategy is not valid');
        }

        // main transport
        $mailer = new sfMailer($dispatcher, array(
            'logging' => true,
            'delivery_strategy' => 'realtime',
            'transport' => array('class' => 'TestMailerTransport', 'param' => array('foo' => 'bar', 'bar' => 'foo')),
        ));
        $this->is($mailer->getTransport()->getFoo(), 'bar', '__construct() passes the parameters to the main transport');

        // spool
        $mailer = new sfMailer($dispatcher, array(
            'logging' => true,
            'delivery_strategy' => 'spool',
            'spool_class' => 'TestSpool',
            'spool_arguments' => array('TestMailMessage'),
            'transport' => array('class' => 'Swift_SmtpTransport', 'param' => array('username' => 'foo')),
        ));
        $this->is($mailer->getRealTimeTransport()->getUsername(), 'foo', '__construct() passes the parameters to the main transport');

        try {
            $mailer = new sfMailer($dispatcher, array('delivery_strategy' => 'spool'));

            $this->fail('__construct() throws an InvalidArgumentException exception if the spool_class option is not set with the spool delivery strategy');
        } catch (InvalidArgumentException $e) {
            $this->pass('__construct() throws an InvalidArgumentException exception if the spool_class option is not set with the spool delivery strategy');
        }

        $mailer = new sfMailer($dispatcher, array('delivery_strategy' => 'spool', 'spool_class' => 'TestSpool'));
        $this->is(get_class($mailer->getTransport()), 'Swift_SpoolTransport', '__construct() recognizes the spool delivery strategy');
        $this->is(get_class($mailer->getTransport()->getSpool()), 'TestSpool', '__construct() recognizes the spool delivery strategy');

        // single address
        try {
            $mailer = new sfMailer($dispatcher, array('delivery_strategy' => 'single_address'));

            $this->fail('__construct() throws an InvalidArgumentException exception if the delivery_address option is not set with the spool single_address strategy');
        } catch (InvalidArgumentException $e) {
            $this->pass('__construct() throws an InvalidArgumentException exception if the delivery_address option is not set with the spool single_address strategy');
        }

        $mailer = new sfMailer($dispatcher, array('delivery_strategy' => 'single_address', 'delivery_address' => 'foo@example.com'));
        $this->is($mailer->getDeliveryAddress(), 'foo@example.com', '__construct() recognizes the single_address delivery strategy');

        // logging
        $mailer = new sfMailer($dispatcher, array('logging' => false));
        $this->is($mailer->getLogger(), null, '__construct() disables logging if the logging option is set to false');
        $mailer = new sfMailer($dispatcher, array('logging' => true));
        $this->ok($mailer->getLogger() instanceof sfMailerMessageLoggerPlugin, '__construct() enables logging if the logging option is set to true');

        // ->compose()
        $this->diag('->compose()');
        $mailer = new sfMailer($dispatcher, array('delivery_strategy' => 'none'));
        $this->ok($mailer->compose() instanceof Swift_Message, '->compose() returns a Swift_Message instance');
        $message = $mailer->compose('from@example.com', 'to@example.com', 'Subject', 'Body');
        $this->is($message->getFrom(), array('from@example.com' => null), '->compose() takes the from address as its first argument');
        $this->is($message->getTo(), array('to@example.com' => null), '->compose() takes the to address as its second argument');
        $this->is($message->getSubject(), 'Subject', '->compose() takes the subject as its third argument');
        $this->is($message->getBody(), 'Body', '->compose() takes the body as its fourth argument');

        // ->composeAndSend()
        $this->diag('->composeAndSend()');
        $mailer = new sfMailer($dispatcher, array('logging' => true, 'delivery_strategy' => 'none'));
        $mailer->composeAndSend('from@example.com', 'to@example.com', 'Subject', 'Body');
        $this->is($mailer->getLogger()->countMessages(), 1, '->composeAndSend() composes and sends the message');
        $messages = $mailer->getLogger()->getMessages();
        $this->is($messages[0]->getFrom(), array('from@example.com' => null), '->composeAndSend() takes the from address as its first argument');
        $this->is($messages[0]->getTo(), array('to@example.com' => null), '->composeAndSend() takes the to address as its second argument');
        $this->is($messages[0]->getSubject(), 'Subject', '->composeAndSend() takes the subject as its third argument');
        $this->is($messages[0]->getBody(), 'Body', '->composeAndSend() takes the body as its fourth argument');

        // ->flushQueue()
        $this->diag('->flushQueue()');
        $mailer = new sfMailer($dispatcher, array('delivery_strategy' => 'none'));
        $mailer->composeAndSend('from@example.com', 'to@example.com', 'Subject', 'Body');
        try {
            $mailer->flushQueue();

            $this->fail('->flushQueue() throws a LogicException exception if the delivery_strategy is not spool');
        } catch (LogicException $e) {
            $this->pass('->flushQueue() throws a LogicException exception if the delivery_strategy is not spool');
        }

        $mailer = new sfMailer($dispatcher, array(
            'delivery_strategy' => 'spool',
            'spool_class' => 'TestSpool',
            'spool_arguments' => array('TestMailMessage'),
            'transport' => array('class' => 'TestMailerTransport'),
        ));
        $transport = $mailer->getRealtimeTransport();
        $spool = $mailer->getTransport()->getSpool();

        $mailer->composeAndSend('from@example.com', 'to@example.com', 'Subject', 'Body');
        $this->is($spool->getQueuedCount(), 1, '->flushQueue() sends messages in the spool');
        $this->is($transport->getSentCount(), 0, '->flushQueue() sends messages in the spool');
        $mailer->flushQueue();
        $this->is($spool->getQueuedCount(), 0, '->flushQueue() sends messages in the spool');
        $this->is($transport->getSentCount(), 1, '->flushQueue() sends messages in the spool');

        // ->sendNextImmediately()
        $this->diag('->sendNextImmediately()');
        $mailer = new sfMailer($dispatcher, array(
            'logging' => true,
            'delivery_strategy' => 'spool',
            'spool_class' => 'TestSpool',
            'spool_arguments' => array('TestMailMessage'),
            'transport' => array('class' => 'TestMailerTransport'),
        ));
        $transport = $mailer->getRealtimeTransport();
        $spool = $mailer->getTransport()->getSpool();
        $this->is($mailer->sendNextImmediately(), $mailer, '->sendNextImmediately() implements a fluid interface');
        $mailer->composeAndSend('from@example.com', 'to@example.com', 'Subject', 'Body');
        $this->is($spool->getQueuedCount(), 0, '->sendNextImmediately() bypasses the spool');
        $this->is($transport->getSentCount(), 1, '->sendNextImmediately() bypasses the spool');
        $transport->reset();
        $spool->reset();

        $mailer->composeAndSend('from@example.com', 'to@example.com', 'Subject', 'Body');
        $this->is($spool->getQueuedCount(), 1, '->sendNextImmediately() bypasses the spool but only for the very next message');
        $this->is($transport->getSentCount(), 0, '->sendNextImmediately() bypasses the spool but only for the very next message');

        // ->getDeliveryAddress() ->setDeliveryAddress()
        $this->diag('->getDeliveryAddress() ->setDeliveryAddress()');
        $mailer = new sfMailer($dispatcher, array('delivery_strategy' => 'none'));
        $mailer->setDeliveryAddress('foo@example.com');
        $this->is($mailer->getDeliveryAddress(), 'foo@example.com', '->setDeliveryAddress() sets the delivery address for the single_address strategy');

        // ->getLogger() ->setLogger()
        $this->diag('->getLogger() ->setLogger()');
        $mailer = new sfMailer($dispatcher, array('delivery_strategy' => 'none'));
        $mailer->setLogger($logger = new sfMailerMessageLoggerPlugin($dispatcher));
        $this->ok($mailer->getLogger() === $logger, '->setLogger() sets the mailer logger');

        // ->getDeliveryStrategy()
        $this->diag('->getDeliveryStrategy()');
        $mailer = new sfMailer($dispatcher, array('delivery_strategy' => 'none'));
        $this->is($mailer->getDeliveryStrategy(), 'none', '->getDeliveryStrategy() returns the delivery strategy');

        // ->getRealtimeTransport() ->setRealtimeTransport()
        $this->diag('->getRealtimeTransport() ->setRealtimeTransport()');
        $mailer = new sfMailer($dispatcher, array('delivery_strategy' => 'none'));
        $mailer->setRealtimeTransport($transport = new TestMailerTransport());
        $this->ok($mailer->getRealtimeTransport() === $transport, '->setRealtimeTransport() sets the mailer transport');
    }
}
