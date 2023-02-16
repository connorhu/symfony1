<?php

require_once __DIR__.'/common.inc';

$context = sfContext::getInstance();
$dispatcher = $context->getEventDispatcher();

$parameterHolder = new sfViewParameterHolder(new sfEventDispatcher(), array('foo' => 'bar'));
/** @var sfViewParameterHolder $unserialized */
$unserialized = unserialize(serialize($parameterHolder));
echo implode(',', array_keys($parameterHolder->toArray()));
echo "\n";
echo implode(',', array_keys($unserialized->toArray()));
echo "\n";
echo $parameterHolder->toArray()['foo'];
echo "\n";
echo $unserialized->toArray()['foo'];
echo "\n";
echo $parameterHolder->toArray()['sf_data']['foo'];
echo "\n";
echo $unserialized->toArray()['sf_data']['foo'];
