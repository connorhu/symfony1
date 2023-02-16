<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TestForm extends sfFormSymfony
{
    public function configure()
    {
        $this->setValidators(array(
            'first_name' => new sfValidatorString(),
            'last_name' => new sfValidatorString(),
        ));
    }
}
