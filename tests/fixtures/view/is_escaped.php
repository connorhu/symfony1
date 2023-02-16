<?php

require_once __DIR__.'/common.inc';

$context = sfContext::getInstance();
$dispatcher = $context->getEventDispatcher();

$parameterHolder = new sfViewParameterHolder($dispatcher);
$parameterHolder->initialize($dispatcher, array(), array('escaping_strategy' => 'on', 'escaping_method' => 'ESC_RAW'));

$parameterHolder->setEscaping('on');
var_export($parameterHolder->isEscaped());
echo "\n";
$parameterHolder->setEscaping('off');
var_export($parameterHolder->isEscaped());
