<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myPatternRouting extends sfPatternRouting
{
    public function initialize(sfEventDispatcher $dispatcher, sfCache $cache = null, $options = array())
    {
        parent::initialize($dispatcher, $cache, $options);

        $this->options['context']['host'] = 'localhost';
    }

    public function parse($url)
    {
        $parameters = parent::parse($url);
        unset($parameters['_sf_route']);

        return $parameters;
    }

    public function getCurrentRouteName()
    {
        return $this->currentRouteName;
    }

    public function isRouteLoaded($name)
    {
        return isset($this->routes[$name]) && is_object($this->routes[$name]);
    }

    protected function getConfigFileName()
    {
        return __DIR__.'/config_routing.yml.php';
    }
}
