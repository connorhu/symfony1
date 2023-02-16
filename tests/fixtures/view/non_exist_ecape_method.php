<?php

require_once __DIR__.'/common.inc';

$context = sfContext::getInstance();
$dispatcher = $context->getEventDispatcher();

$parameterHolder = new sfViewParameterHolder($dispatcher);
$parameterHolder->initialize($dispatcher);
$parameterHolder->setEscaping('on');

try {
    $parameterHolder->setEscapingMethod('nonexistant');
    $parameterHolder->getEscapingMethod();
    echo 'fail';
} catch (InvalidArgumentException $e) {
    echo get_class($e);
}

$parameterHolder = new sfViewParameterHolder(new sfEventDispatcher(), array('foo' => 'bar'));
$parameterHolder->setEscaping('null');
try {
    $parameterHolder->toArray();
    echo 'fail';
} catch (InvalidArgumentException $e) {
    echo "\n";
    echo get_class($e);
}