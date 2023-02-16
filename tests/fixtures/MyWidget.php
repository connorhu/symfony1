<?php

class MyWidget extends sfWidgetForm
{
    protected function configure($options = array(), $attributes = array())
    {
        $this->addRequiredOption('name');
    }

    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        return null;
    }

    public function getJavaScripts()
    {
        return array('/path/to/a/'.$this->getOption('name').'.js');
    }

    public function getStylesheets()
    {
        return array('/path/to/a/'.$this->getOption('name').'.css' => 'all');
    }
}
