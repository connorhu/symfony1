<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class OptionsTest1Task extends BaseTestTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('none', null, sfCommandOption::PARAMETER_NONE),
            new sfCommandOption('required', null, sfCommandOption::PARAMETER_REQUIRED),
            new sfCommandOption('optional', null, sfCommandOption::PARAMETER_OPTIONAL),
            new sfCommandOption('array', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY),
        ));
    }
}
