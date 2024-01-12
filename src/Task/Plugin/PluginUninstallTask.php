<?php

namespace Symfony1\Components\Task\Plugin;

use Symfony1\Components\Command\CommandArgument;
use Symfony1\Components\Command\CommandOption;
use function sprintf;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Uninstall a plugin.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PluginUninstallTask extends PluginBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(new CommandArgument('name', CommandArgument::REQUIRED, 'The plugin name')));
        $this->addOptions(array(new CommandOption('channel', 'c', CommandOption::PARAMETER_REQUIRED, 'The PEAR channel name', null), new CommandOption('install_deps', 'd', CommandOption::PARAMETER_NONE, 'Whether to force installation of dependencies', null)));
        $this->namespace = 'plugin';
        $this->name = 'uninstall';
        $this->briefDescription = 'Uninstalls a plugin';
        $this->detailedDescription = <<<'EOF'
The [plugin:uninstall|INFO] task uninstalls a plugin:

  [./symfony plugin:uninstall sfGuardPlugin|INFO]

The default channel is [symfony|INFO].

You can also uninstall a plugin which has a different channel:

  [./symfony plugin:uninstall --channel=mypearchannel sfGuardPlugin|INFO]

  [./symfony plugin:uninstall -c mypearchannel sfGuardPlugin|INFO]

Or you can use the [channel/package|INFO] notation:

  [./symfony plugin:uninstall mypearchannel/sfGuardPlugin|INFO]

You can get the PEAR channel name of a plugin by launching the
[plugin:list] task.

If the plugin contains some web content (images, stylesheets or javascripts),
the task also removes the [web/%name%|COMMENT] symbolic link (on *nix)
or directory (on Windows).
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $this->logSection('plugin', sprintf('uninstalling plugin "%s"', $arguments['name']));
        $this->getPluginManager()->uninstallPlugin($arguments['name'], $options['channel']);
    }
}
class_alias(PluginUninstallTask::class, 'sfPluginUninstallTask', false);