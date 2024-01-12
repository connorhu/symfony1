<?php

namespace Symfony1\Components\Validator;

use ReflectionClass;
use function sprintf;
use function is_object;
use function in_array;
use function get_class;
use function implode;
use function array_map;
use function var_export;
use function array_merge;
class ValidatorFDTokenOperator
{
    protected $class;
    protected $operator;
    protected $token;
    protected $arguments = array();
    public function __construct($operator, $arguments = array())
    {
        $this->operator = $operator;
        $this->arguments = $arguments;
        $this->class = 'or' == $operator ? 'sfValidatorOr' : 'sfValidatorAnd';
    }
    public function __toString()
    {
        return $this->operator;
    }
    public function asPhp($tokenLeft, $tokenRight)
    {
        return sprintf('new %s(array(%s, %s), %s)', $this->class, is_object($tokenLeft) && in_array(get_class($tokenLeft), array('sfValidatorFDToken', 'sfValidatorFDTokenFilter')) ? $tokenLeft->asPhp() : $tokenLeft, is_object($tokenRight) && in_array(get_class($tokenRight), array('sfValidatorFDToken', 'sfValidatorFDTokenFilter')) ? $tokenRight->asPhp() : $tokenRight, implode(', ', array_map(function ($a) {
            return var_export($a, true);
        }, $this->arguments)));
    }
    public function getValidator($tokenLeft, $tokenRight)
    {
        $reflection = new ReflectionClass($this->class);
        $validators = array(in_array(get_class($tokenLeft), array('sfValidatorFDToken', 'sfValidatorFDTokenFilter')) ? $tokenLeft->getValidator() : $tokenLeft, in_array(get_class($tokenRight), array('sfValidatorFDToken', 'sfValidatorFDTokenFilter')) ? $tokenRight->getValidator() : $tokenRight);
        return $reflection->newInstanceArgs(array_merge(array($validators), $this->arguments));
    }
}
class_alias(ValidatorFDTokenOperator::class, 'sfValidatorFDTokenOperator', false);