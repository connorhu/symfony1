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
require_once __DIR__.'/../fixtures/myResponse2.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfResponseTest extends sfEventDispatcherTestCase
{
    use PhpUnitSfTestHelperTrait;

    protected function setUp(): void
    {
        $this->dispatcher = new sfEventDispatcher();
        $this->testObject = new myResponse2($this->dispatcher, array('foo' => 'bar'));
        $this->class = 'response';
    }

    public function testTodoMigrate()
    {
        $dispatcher = $this->dispatcher;

        // ->initialize()
        $this->diag('->initialize()');
        $response = new myResponse2($dispatcher, array('foo' => 'bar'));
        $options = $response->getOptions();
        $this->is($options['foo'], 'bar', '->initialize() takes an array of options as its second argument');
        $this->is($options['logging'], false, '->getOptions() returns options for response instance');

        // ->getContent() ->setContent()
        $this->diag('->getContent() ->setContent()');
        $this->is($response->getContent(), null, '->getContent() returns the current response content which is null by default');
        $response->setContent('test');
        $this->is($response->getContent(), 'test', '->setContent() sets the response content');

        // ->sendContent()
        $this->diag('->sendContent()');
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        $this->is($content, 'test', '->sendContent() output the current response content');

        // ->serialize() ->unserialize()
        $this->diag('->serialize() ->unserialize()');
        $this->ok(new myResponse2($dispatcher) instanceof Serializable, 'sfResponse implements the Serializable interface');
    }
}
