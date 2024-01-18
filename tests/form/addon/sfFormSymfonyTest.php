<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../PhpUnitSfTestHelperTrait.php';
require_once __DIR__.'/../../fixtures/FormListener.php';
require_once __DIR__.'/../../fixtures/TestForm.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfFormSymfonyTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $listener = new FormListener();
        $dispatcher = new sfEventDispatcher();

        $dispatcher->connect('form.post_configure', array($listener, 'listen'));
        $dispatcher->connect('form.filter_values', array($listener, 'filter'));
        $dispatcher->connect('form.validation_error', array($listener, 'listen'));

        sfFormSymfony::setEventDispatcher($dispatcher);

        // ->__construct()
        $this->diag('->__construct()');

        $listener->reset();
        $form = new TestForm();
        $this->is(count($listener->events), 1, '->__construct() notifies one event');
        $this->is($listener->events[0][0]->getName(), 'form.post_configure', '->__construct() notifies the "form.post_configure" event');

        // ->bind()
        $this->diag('->bind()');

        $form = new TestForm();
        $listener->reset();
        $form->bind(array(
            'first_name' => 'John',
            'last_name' => 'Doe',
        ));

        $this->is(count($listener->events), 1, '->bind() notifies one event when validation is successful');
        $this->is($listener->events[0][0]->getName(), 'form.filter_values', '->bind() notifies the "form.filter_values" event');
        $this->is_deeply($listener->events[0][1], array('first_name' => 'John', 'last_name' => 'Doe'), '->bind() filters the tainted values');

        $form = new TestForm();
        $listener->reset();
        $form->bind();

        $this->is(count($listener->events), 2, '->bind() notifies two events when validation fails');
        $this->is($listener->events[1][0]->getName(), 'form.validation_error', '->bind() notifies the "form.validation_error" event');
        $this->isa_ok($listener->events[1][0]['error'], 'sfValidatorErrorSchema', '->bind() notifies the error schema');
    }
}
