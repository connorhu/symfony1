<?php

class CompileCheckRoute extends sfRoute
{
    public function isCompiled()
    {
        return $this->compiled;
    }
}
