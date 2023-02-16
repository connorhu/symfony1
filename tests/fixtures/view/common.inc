<?php

require_once __DIR__.'/../../bootstrap.php';
require_once __DIR__.'/../../sfContext.class.php';

function esc_specialchars($value)
{
    return "-ESCAPED-{$value}-ESCAPED-";
}
define('ESC_SPECIALCHARS', 'esc_specialchars');

function esc_raw($value)
{
    return $value;
}
define('ESC_RAW', 'esc_raw');

