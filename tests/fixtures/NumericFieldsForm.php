<?php

class NumericFieldsForm extends sfForm
{
    public function configure()
    {
        $this->setWidgets(array(
            '5' => new sfWidgetFormInputText(),
        ));

        $this->setValidators(array(
            '5' => new sfValidatorString(),
        ));

        $this->widgetSchema->setLabels(array('5' => 'label'.$this->getOption('salt')));
        $this->widgetSchema->setHelps(array('5' => 'help'.$this->getOption('salt')));
    }
}
