<?php

require_once __DIR__.'/common.inc';

$context = sfContext::getInstance();
$dispatcher = $context->getEventDispatcher();

$parameterHolder = new sfViewParameterHolder(new sfEventDispatcher(), array('foo' => 'bar'));
$parameterHolder->setEscaping('off');
$values = $parameterHolder->toArray();

echo count($values);
echo "\n";
echo count($values['sf_data']);
echo "\n";
echo $values['foo'];
echo "\n";
echo $values['sf_data']['foo'];
