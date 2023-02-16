<?php

/**
 * @internal
 *
 * @coversNothing
 */
class TestForm4 extends FormTest
{
    public function configure()
    {
        $this->enableLocalCSRFProtection($this->getOption('csrf_secret'));
    }
}
