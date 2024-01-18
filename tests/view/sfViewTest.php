<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../PhpUnitSfTestHelperTrait.php';
require_once __DIR__.'/../sfEventDispatcherTestCase.php';
require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../fixtures/myView.php';
require_once __DIR__.'/../fixtures/configuredView.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfViewTest extends sfEventDispatcherTestCase
{
    use PhpUnitSfTestHelperTrait;

    protected $context;
    protected $view;

    protected function setUp(): void
    {
        $this->context = sfContext::getInstance(array('request' => 'sfWebRequest', 'response' => 'sfWebResponse'));
        $this->dispatcher = $this->context->getEventDispatcher();
        $this->view = new myView($this->context, '', '', '');
        $this->testObject = $this->view;
        $this->class = 'view';
    }

    public function testTodoMigrate()
    {
        $context = sfContext::getInstance(array('request' => 'sfWebRequest', 'response' => 'sfWebResponse'), true);

        function configure_format(sfEvent $event)
        {
            $event->getSubject()->setDecorator(true);
            $event['response']->setContentType('application/javascript');

            return true;
        }

        // ->isDecorator() ->setDecorator()
        $this->diag('->isDecorator() ->setDecorator()');
        $this->is($this->view->isDecorator(), false, '->isDecorator() returns true if the current view have to be decorated');
        $this->view->setDecorator(true);
        $this->is($this->view->isDecorator(), true, '->setDecorator() sets the decorator status for the view');

        // format
        $this->diag('format');
        $context = sfContext::getInstance(array('request' => 'sfWebRequest', 'response' => 'sfWebResponse'), true);
        $context->getRequest()->setFormat('js', 'application/x-javascript');
        $context->getRequest()->setRequestFormat('js');
        configuredView::$isDecorated = true;
        $this->view = new configuredView($context, '', '', '');
        $this->is($this->view->isDecorator(), false, '->initialize() uses the format to configure the view');
        $this->is($context->getResponse()->getContentType(), 'application/x-javascript', '->initialize() uses the format to configure the view');
        $this->is($this->view->getExtension(), '.js.php', '->initialize() uses the format to configure the view');
        $context = sfContext::getInstance(array('request' => 'sfWebRequest', 'response' => 'sfWebResponse'), true);
        $context->getEventDispatcher()->connect('view.configure_format', 'configure_format');

        $context->getRequest()->setRequestFormat('js');
        configuredView::$isDecorated = true;
        $this->view = new configuredView($context, '', '', '');
        $this->is($this->view->isDecorator(), true, '->initialize() uses the format to configure the view');
        $this->is($context->getResponse()->getContentType(), 'application/javascript', '->initialize() uses the format to configure the view');
    }
}
