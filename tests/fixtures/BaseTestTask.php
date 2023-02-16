<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class BaseTestTask extends sfTask
{
    public $lastArguments = array();
    public $lastOptions = array();

    public function __construct()
    {
        // lazy constructor
        parent::__construct(new sfEventDispatcher(), new sfFormatter());
    }

    protected function execute($arguments = array(), $options = array())
    {
        $this->lastArguments = $arguments;
        $this->lastOptions = $options;
    }
}
