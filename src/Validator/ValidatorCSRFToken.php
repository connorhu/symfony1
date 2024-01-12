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
 * sfValidatorCSRFToken checks that the token is valid.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class ValidatorCSRFToken extends ValidatorBase
{
    /**
     * @see sfValidatorBase
     */
    protected function configure($options = array(), $messages = array())
    {
        $this->addRequiredOption('token');
        $this->setOption('required', true);
        $this->addMessage('csrf_attack', 'CSRF attack detected.');
    }
    /**
     * @see sfValidatorBase
     */
    protected function doClean($value)
    {
        if ($value != $this->getOption('token')) {
            throw new ValidatorError($this, 'csrf_attack');
        }
        return $value;
    }
}
class_alias(ValidatorCSRFToken::class, 'sfValidatorCSRFToken', false);