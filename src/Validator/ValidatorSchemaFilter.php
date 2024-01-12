<?php

namespace Symfony1\Components\Validator;

use InvalidArgumentException;
use function sprintf;
use function str_repeat;
use function is_array;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfValidatorSchemaFilter executes non schema validator on a schema input value.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class ValidatorSchemaFilter extends ValidatorSchema
{
    /**
     * Constructor.
     *
     * @param string $field The field name
     * @param ValidatorBase $validator The validator
     * @param array $options An array of options
     * @param array $messages An array of error messages
     *
     * @see sfValidatorBase
     */
    public function __construct($field, ValidatorBase $validator, $options = array(), $messages = array())
    {
        $this->addOption('field', $field);
        $this->addOption('validator', $validator);
        parent::__construct(null, $options, $messages);
    }
    /**
     * @see sfValidatorBase
     */
    public function asString($indent = 0)
    {
        return sprintf('%s%s:%s', str_repeat(' ', $indent), $this->getOption('field'), $this->getOption('validator')->asString(0));
    }
    /**
     * @see sfValidatorBase
     */
    protected function doClean($values)
    {
        if (null === $values) {
            $values = array();
        }
        if (!is_array($values)) {
            throw new InvalidArgumentException(sprintf('You must pass an array parameter to the clean() method for filter field "%s"', $this->getOption('field')));
        }
        $value = isset($values[$this->getOption('field')]) ? $values[$this->getOption('field')] : null;
        try {
            $values[$this->getOption('field')] = $this->getOption('validator')->clean($value);
        } catch (ValidatorError $error) {
            $errorSchema = new ValidatorErrorSchema($this);
            $errorSchema->addError($error, $this->getOption('field'));
            throw $errorSchema;
        }
        return $values;
    }
}
class_alias(ValidatorSchemaFilter::class, 'sfValidatorSchemaFilter', false);