<?php

namespace Symfony1\Components\Validator;

class ValidatorFileMulti extends ValidatorFile
{
    /**
     * @see sfValidatorBase
     */
    protected function doClean($value)
    {
        $clean = array();
        foreach ($value as $file) {
            $clean[] = parent::doClean($file);
        }
        return $clean;
    }
}
class_alias(ValidatorFileMulti::class, 'sfValidatorFileMulti', false);