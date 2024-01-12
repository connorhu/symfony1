<?php

namespace Symfony1\Components\Validator;

class ValidatorFDTokenLeftBracket
{
    public function __toString()
    {
        return '(';
    }
}
class_alias(ValidatorFDTokenLeftBracket::class, 'sfValidatorFDTokenLeftBracket', false);