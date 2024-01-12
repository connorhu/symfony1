<?php

namespace Symfony1\Components\Validator;

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfValidatorDateTime validates a date and a time. It also converts the input value to a valid date.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class ValidatorDateTime extends ValidatorDate
{
    /**
     * @see sfValidatorDate
     */
    protected function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);
        $this->setOption('with_time', true);
    }
}
class_alias(ValidatorDateTime::class, 'sfValidatorDateTime', false);