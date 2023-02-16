<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myViewConfigHandler extends sfViewConfigHandler
{
    public function setConfiguration($config)
    {
        $this->yamlConfig = self::mergeConfig($config);
    }

    public function addHtmlAsset($viewName = '')
    {
        return parent::addHtmlAsset($viewName);
    }
}
