<?php

namespace Symfony1\Components\Validator;

use ReflectionClass;
use function sprintf;
use function implode;
use function array_map;
use function var_export;
class ValidatorFDToken
{
    protected $class;
    protected $arguments;
    public function __construct($class, $arguments = array())
    {
        $this->class = $class;
        $this->arguments = $arguments;
    }
    public function asPhp()
    {
        return sprintf('new %s(%s)', $this->class, implode(', ', array_map(function ($a) {
            return var_export($a, true);
        }, $this->arguments)));
    }
    public function getValidator()
    {
        $reflection = new ReflectionClass($this->class);
        return $reflection->newInstanceArgs($this->arguments);
    }
}
class_alias(ValidatorFDToken::class, 'sfValidatorFDToken', false);