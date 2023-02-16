<?php

class myObjectRoute extends sfObjectRoute
{
    protected function doConvertObjectToArray($object)
    {
        $parameters = array();
        foreach ($this->getRealVariables() as $variable) {
            if (method_exists($object, $method = 'get'.$variable)) {
                $parameters[$variable] = $object->{$method}();
            }
        }

        return $parameters;
    }
}
