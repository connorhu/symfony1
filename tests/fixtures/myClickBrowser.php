<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myClickBrowser extends sfBrowser
{
    public function setHtml($html)
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->validateOnParse = true;
        $this->dom->loadHTML($html);
        $this->domCssSelector = new sfDomCssSelector($this->dom);
    }

    public function getFiles()
    {
        $f = $this->files;
        $this->files = array();

        return $f;
    }

    public function call($uri, $method = 'get', $parameters = array(), $changeStack = true)
    {
        $uri = $this->fixUri($uri);

        $this->fields = array();

        return array($method, $uri, $parameters);
    }

    public function getDefaultServerArray($name)
    {
        return isset($this->defaultServerArray[$name]) ? $this->defaultServerArray[$name] : false;
    }
}
