<?php

require_once __DIR__.'/common.inc';

$context = sfContext::getInstance();
$dispatcher = $context->getEventDispatcher();

$parameterHolder = new sfViewParameterHolder($dispatcher);
$parameterHolder->initialize($dispatcher, array(), array('escaping_strategy' => 'on', 'escaping_method' => 'ESC_RAW'));

echo $parameterHolder->getEscaping();
echo "\n";
echo $parameterHolder->getEscapingMethod();
