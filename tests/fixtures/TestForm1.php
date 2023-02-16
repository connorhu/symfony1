<?php

/**
 * @internal
 *
 * @coversNothing
 */
class TestForm1 extends FormTest
{
    public function configure()
    {
        $this->disableCSRFProtection();
        $this->setWidgets(array(
            'a' => new sfWidgetFormInputText(),
            'b' => new sfWidgetFormInputText(),
            'c' => new sfWidgetFormInputText(),
        ));
        $this->setValidators(array(
            'a' => new sfValidatorString(array('min_length' => 2)),
            'b' => new sfValidatorString(array('max_length' => 3)),
            'c' => new sfValidatorString(array('max_length' => 1000)),
        ));
        $this->getWidgetSchema()->setLabels(array(
            'a' => '1_a',
            'b' => '1_b',
            'c' => '1_c',
        ));
        $this->getWidgetSchema()->setHelps(array(
            'a' => '1_a',
            'b' => '1_b',
            'c' => '1_c',
        ));
    }
}
