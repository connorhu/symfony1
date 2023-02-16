<?php

require_once __DIR__.'/common.inc';

$context = sfContext::getInstance();
$dispatcher = $context->getEventDispatcher();

$parameterHolder = new sfViewParameterHolder($dispatcher);
$parameterHolder->initialize($dispatcher, array(), array('escaping_strategy' => 'on', 'escaping_method' => 'ESC_RAW'));

$parameterHolder->setEscapingMethod('ESC_RAW');
echo $parameterHolder->getEscapingMethod();
echo "\n";
var_export($parameterHolder->getEscapingMethod() === ESC_RAW);

$parameterHolder->setEscapingMethod('');
echo "\n";
var_export($parameterHolder->getEscapingMethod());
