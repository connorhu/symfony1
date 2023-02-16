<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myRequest4 extends sfWebRequest
{
    protected static $initialPathArrayKeys;

    public $languages;
    public $charsets;
    public $acceptableContentTypes;

    public function initialize(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array(), $options = array())
    {
        if (isset($options['content_custom_only_for_test'])) {
            $this->content = $options['content_custom_only_for_test'];
            unset($options['content_custom_only_for_test']);
        }

        parent::initialize($dispatcher, $parameters, $attributes, $options);

        if (null === self::$initialPathArrayKeys) {
            self::$initialPathArrayKeys = array_keys($this->getPathInfoArray());
        }

        $this->resetPathInfoArray();
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function resetPathInfoArray()
    {
        foreach (array_diff(array_keys($this->getPathInfoArray()), self::$initialPathArrayKeys) as $key) {
            unset($this->pathInfoArray[$key]);
        }
    }
}
