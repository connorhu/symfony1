<?php

/**
 * @internal
 *
 * @coversNothing
 */
class TestForm3 extends FormTest
{
    public function configure()
    {
        $this->disableLocalCSRFProtection();
    }
}
