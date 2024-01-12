<?php

namespace Symfony1\Components\Filter;

use Symfony1\Components\Util\ParameterHolder;
use Symfony1\Components\Util\Context;
use function get_class;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfFilter provides a way for you to intercept incoming requests or outgoing responses.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Sean Kerr <sean@code-box.org>
 *
 * @version SVN: $Id$
 */
abstract class Filter
{
    /**
     * @var bool[]
     */
    public static $filterCalled = array();
    /**
     * @var ParameterHolder
     */
    protected $parameterHolder;
    /**
     * @var Context
     */
    protected $context;
    /**
     * Class constructor.
     *
     * @see initialize()
     *
     * @param Context $context
     * @param array $parameters
     */
    public function __construct($context, $parameters = array())
    {
        $this->initialize($context, $parameters);
    }
    /**
     * Initializes this Filter.
     *
     * @param Context $context The current application context
     * @param array $parameters An associative array of initialization parameters
     *
     * @return (bool | void) true
     */
    public function initialize($context, $parameters = array())
    {
        $this->context = $context;
        $this->parameterHolder = new ParameterHolder();
        $this->parameterHolder->add($parameters);
        return true;
    }
    /**
     * Retrieves the current application context.
     *
     * @return Context The current sfContext instance
     */
    public final function getContext()
    {
        return $this->context;
    }
    /**
     * Gets the parameter holder for this object.
     *
     * @return ParameterHolder A sfParameterHolder instance
     */
    public function getParameterHolder()
    {
        return $this->parameterHolder;
    }
    /**
     * Gets the parameter associated with the given key.
     *
     * This is a shortcut for:
     *
     * <code>$this->getParameterHolder()->get()</code>
     *
     * @param string $name The key name
     * @param string $default The default value
     *
     * @return string The value associated with the key
     *
     * @see sfParameterHolder
     */
    public function getParameter($name, $default = null)
    {
        return $this->parameterHolder->get($name, $default);
    }
    /**
     * Returns true if the given key exists in the parameter holder.
     *
     * This is a shortcut for:
     *
     * <code>$this->getParameterHolder()->has()</code>
     *
     * @param string $name The key name
     *
     * @return bool true if the given key exists, false otherwise
     *
     * @see sfParameterHolder
     */
    public function hasParameter($name)
    {
        return $this->parameterHolder->has($name);
    }
    /**
     * Sets the value for the given key.
     *
     * This is a shortcut for:
     *
     * <code>$this->getParameterHolder()->set()</code>
     *
     * @param string $name The key name
     * @param string $value The value
     *
     * @see sfParameterHolder
     */
    public function setParameter($name, $value)
    {
        return $this->parameterHolder->set($name, $value);
    }
    /**
     * Returns true if this is the first call to the sfFilter instance.
     *
     * @return bool true if this is the first call to the sfFilter instance, false otherwise
     */
    protected function isFirstCall()
    {
        $class = get_class($this);
        if (isset(self::$filterCalled[$class])) {
            return false;
        }
        self::$filterCalled[$class] = true;
        return true;
    }
}
class_alias(Filter::class, 'sfFilter', false);