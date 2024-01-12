<?php

namespace Symfony1\Components\Plugin;

use PEAR_Config;
use function str_replace;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
// require_once 'PEAR/Config.php';
/**
 * sfPearConfig.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PearConfig extends PEAR_Config
{
    public function &getREST($version, $options = array())
    {
        $class = 'sfPearRest' . str_replace('.', '', $version);
        $instance = new $class($this, $options);
        return $instance;
    }
}
class_alias(PearConfig::class, 'sfPearConfig', false);