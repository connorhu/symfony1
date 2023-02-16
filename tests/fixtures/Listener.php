<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Listener
{
    protected $value = '';

    public function filterFoo(sfEvent $event, $foo)
    {
        return "*{$foo}*";
    }

    public function filterFooBis(sfEvent $event, $foo)
    {
        return "-{$foo}-";
    }

    public function listenToFoo(sfEvent $event)
    {
        $this->value .= 'listenToFoo';
    }

    public function listenToFooBis(sfEvent $event)
    {
        $this->value .= 'listenToFooBis';

        return true;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function reset()
    {
        $this->value = '';
    }
}
