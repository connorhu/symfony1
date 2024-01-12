<?php

namespace Symfony1\Components\Config;

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfPluginConfigurationGeneric represents a configuration for a plugin with no configuration class.
 *
 * @author Kris Wallsmith <kris.wallsmith@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PluginConfigurationGeneric extends PluginConfiguration
{
    /**
     * @see sfPluginConfiguration
     */
    public function initialize()
    {
        return false;
    }
}
class_alias(PluginConfigurationGeneric::class, 'sfPluginConfigurationGeneric', false);