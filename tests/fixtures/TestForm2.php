<?php

/**
 * @internal
 *
 * @coversNothing
 */
class TestForm2 extends FormTest
{
    public function configure()
    {
        $this->disableCSRFProtection();
        $this->setWidgets(array(
            'c' => new sfWidgetFormTextarea(),
            'd' => new sfWidgetFormTextarea(),
        ));
        $this->setValidators(array(
            'c' => new sfValidatorPass(),
            'd' => new sfValidatorString(array('max_length' => 5)),
        ));
        $this->getWidgetSchema()->setLabels(array(
            'c' => '2_c',
            'd' => '2_d',
        ));
        $this->getWidgetSchema()->setHelps(array(
            'c' => '2_c',
            'd' => '2_d',
        ));
        $this->validatorSchema->setPreValidator(new sfValidatorPass());
        $this->validatorSchema->setPostValidator(new sfValidatorPass());
    }
}
