<?php

namespace Symfony1\Components\Validator;

class ValidatorFDTokenRightBracket
{
    public function __toString()
    {
        return ')';
    }
}
class_alias(ValidatorFDTokenRightBracket::class, 'sfValidatorFDTokenRightBracket', false);