<?php

class MyRoute extends sfRoute
{
    protected function tokenizeBufferBefore(&$buffer, &$tokens, &$afterASeparator, &$currentSeparator)
    {
        if ($afterASeparator && preg_match('#^=('.$this->options['variable_regex'].')#', $buffer, $match)) {
            // a labelled variable
            $this->tokens[] = array('label', $currentSeparator, $match[0], $match[1]);

            $currentSeparator = '';
            $buffer = substr($buffer, strlen($match[0]));
            $afterASeparator = false;
        } else {
            return false;
        }
    }

    protected function compileForLabel($separator, $name, $variable)
    {
        if (!isset($this->requirements[$variable])) {
            $this->requirements[$variable] = $this->options['variable_content_regex'];
        }

        $this->segments[] = preg_quote($separator, '#').$variable.$separator.'(?P<'.$variable.'>'.$this->requirements[$variable].')';
        $this->variables[$variable] = $name;

        if (!isset($this->defaults[$variable])) {
            $this->firstOptional = count($this->segments);
        }
    }

    protected function generateForLabel($optional, $tparams, $separator, $name, $variable)
    {
        if (!empty($tparams[$variable]) && (!$optional || !isset($this->defaults[$variable]) || $tparams[$variable] != $this->defaults[$variable])) {
            return $variable.'/'.urlencode($tparams[$variable]);
        }
    }
}
