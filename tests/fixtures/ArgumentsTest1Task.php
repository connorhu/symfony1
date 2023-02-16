<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ArgumentsTest1Task extends BaseTestTask
{
    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('foo', sfCommandArgument::REQUIRED),
            new sfCommandArgument('bar', sfCommandArgument::OPTIONAL),
        ));
    }
}
