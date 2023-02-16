<?php

require_once __DIR__.'/common.inc';

$context = sfContext::getInstance();
$dispatcher = $context->getEventDispatcher();

$parameterHolder = new sfViewParameterHolder($dispatcher);
$defaultValues = $parameterHolder->getAll();
var_export(is_array($defaultValues));
echo "\n";
var_export(implode(',', $defaultValues));