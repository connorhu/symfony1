<?php

namespace Symfony1\Components\Validator;

use function sprintf;
class ValidatorFDTokenFilter
{
    protected $field;
    protected $token;
    public function __construct($field, ValidatorFDToken $token)
    {
        $this->field = $field;
        $this->token = $token;
    }
    public function asPhp()
    {
        return sprintf('new sfValidatorSchemaFilter(\'%s\', %s)', $this->field, $this->token->asPhp());
    }
    public function getValidator()
    {
        return new ValidatorSchemaFilter($this->field, $this->token->getValidator());
    }
}
class_alias(ValidatorFDTokenFilter::class, 'sfValidatorFDTokenFilter', false);