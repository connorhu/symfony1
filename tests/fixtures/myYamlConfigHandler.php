<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class myYamlConfigHandler extends sfYamlConfigHandler
{
    public $yamlConfig;

    public function execute($configFiles) {}

    public static function parseYamls($configFiles)
    {
        return parent::parseYamls($configFiles);
    }

    public static function parseYaml($configFile)
    {
        return parent::parseYaml($configFile);
    }

    public function mergeConfigValue($keyName, $category)
    {
        return parent::mergeConfigValue($keyName, $category);
    }

    public function getConfigValue($keyName, $category, $defaultValue = null)
    {
        return parent::getConfigValue($keyName, $category, $defaultValue);
    }
}
