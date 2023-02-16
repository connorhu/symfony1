<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MyWidgetWithRequired extends MyWidget4
{
    protected function configure($options = array(), $attributes = array())
    {
        $this->addRequiredOption('foo');
    }
}
