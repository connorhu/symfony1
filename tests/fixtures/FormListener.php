<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class FormListener
{
    public $events = array();

    public function listen(sfEvent $event)
    {
        $this->events[] = func_get_args();
    }

    public function filter(sfEvent $event, $value)
    {
        $this->events[] = func_get_args();

        return $value;
    }

    public function reset()
    {
        $this->events = array();
    }
}
