<?php

/**
 * @internal
 *
 * @coversNothing
 */
class FormTest extends sfForm
{
    public function getCSRFToken($secret = null)
    {
        return "*{$secret}*";
    }
}
