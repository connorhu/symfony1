<?php

namespace Symfony1\Components\Form\Addon;

use Symfony1\Components\Form\Form;
use Symfony1\Components\Event\EventDispatcher;
use Symfony1\Components\Event\Event;
use Symfony1\Components\Exception\Exception;
use Symfony1\Components\Validator\ValidatorError;
use function sprintf;
use function get_class;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Extends the form component with symfony-specific functionality.
 *
 * @author Kris Wallsmith <kris.wallsmith@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class FormSymfony extends Form
{
    /**
     * @var (EventDispatcher | null)
     */
    protected static $dispatcher;
    /**
     * Constructor.
     *
     * Notifies the 'form.post_configure' event.
     *
     * @see sfForm
     *
     * @param (mixed | null) $CSRFSecret
     */
    public function __construct($defaults = array(), $options = array(), $CSRFSecret = null)
    {
        parent::__construct($defaults, $options, $CSRFSecret);
        if (self::$dispatcher) {
            self::$dispatcher->notify(new Event($this, 'form.post_configure'));
        }
    }
    /**
     * Calls methods defined via sfEventDispatcher.
     *
     * @param string $method The method name
     * @param array $arguments The method arguments
     *
     * @return mixed The returned value of the called method
     *
     * @throws Exception
     */
    public function __call($method, $arguments)
    {
        if (self::$dispatcher) {
            $event = self::$dispatcher->notifyUntil(new Event($this, 'form.method_not_found', array('method' => $method, 'arguments' => $arguments)));
            if ($event->isProcessed()) {
                return $event->getReturnValue();
            }
        }
        throw new Exception(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }
    /**
     * Sets the event dispatcher to be used by all forms.
     */
    public static function setEventDispatcher(EventDispatcher $dispatcher = null)
    {
        self::$dispatcher = $dispatcher;
    }
    /**
     * Returns the event dispatcher.
     *
     * @return EventDispatcher
     */
    public static function getEventDispatcher()
    {
        return self::$dispatcher;
    }
    /**
     * Notifies the 'form.filter_values' and 'form.validation_error' events.
     *
     * @see sfForm
     */
    protected function doBind(array $values)
    {
        if (self::$dispatcher) {
            $values = self::$dispatcher->filter(new Event($this, 'form.filter_values'), $values)->getReturnValue();
        }
        try {
            parent::doBind($values);
        } catch (ValidatorError $error) {
            if (self::$dispatcher) {
                self::$dispatcher->notify(new Event($this, 'form.validation_error', array('error' => $error)));
            }
            throw $error;
        }
    }
}
class_alias(FormSymfony::class, 'sfFormSymfony', false);