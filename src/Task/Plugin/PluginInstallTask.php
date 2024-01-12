<?php

namespace Symfony1\Components\Task\Plugin;

use Symfony1\Components\Command\CommandArgument;
use Symfony1\Components\Command\CommandOption;
use Exception;
use Symfony1\Components\Command\CommandException;
use function sprintf;
use function trim;
use function str_replace;
use function strtolower;
use function in_array;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Installs a plugin.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PluginInstallTask extends PluginBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(new CommandArgument('name', CommandArgument::REQUIRED, 'The plugin name')));
        $this->addOptions(array(new CommandOption('stability', 's', CommandOption::PARAMETER_REQUIRED, 'The preferred stability (stable, beta, alpha)', null), new CommandOption('release', 'r', CommandOption::PARAMETER_REQUIRED, 'The preferred version', null), new CommandOption('channel', 'c', CommandOption::PARAMETER_REQUIRED, 'The PEAR channel name', null), new CommandOption('install_deps', 'd', CommandOption::PARAMETER_NONE, 'Whether to force installation of required dependencies', null), new CommandOption('force-license', null, CommandOption::PARAMETER_NONE, 'Whether to force installation even if the license is not MIT like')));
        $this->namespace = 'plugin';
        $this->name = 'install';
        $this->briefDescription = 'Installs a plugin';
        $this->detailedDescription = <<<'EOF'
The [plugin:install|INFO] task installs a plugin:

  [./symfony plugin:install sfGuardPlugin|INFO]

By default, it installs the latest [stable|COMMENT] release.

If you want to install a plugin that is not stable yet,
use the [stability|COMMENT] option:

  [./symfony plugin:install --stability=beta sfGuardPlugin|INFO]
  [./symfony plugin:install -s beta sfGuardPlugin|INFO]

You can also force the installation of a specific version:

  [./symfony plugin:install --release=1.0.0 sfGuardPlugin|INFO]
  [./symfony plugin:install -r 1.0.0 sfGuardPlugin|INFO]

To force installation of all required dependencies, use the [install_deps|INFO] flag:

  [./symfony plugin:install --install-deps sfGuardPlugin|INFO]
  [./symfony plugin:install -d sfGuardPlugin|INFO]

By default, the PEAR channel used is [symfony-plugins|INFO]
(plugins.symfony-project.org).

You can specify another channel with the [channel|COMMENT] option:

  [./symfony plugin:install --channel=mypearchannel sfGuardPlugin|INFO]
  [./symfony plugin:install -c mypearchannel sfGuardPlugin|INFO]

You can also install PEAR packages hosted on a website:

  [./symfony plugin:install http://somewhere.example.com/sfGuardPlugin-1.0.0.tgz|INFO]

Or local PEAR packages:

  [./symfony plugin:install /home/fabien/plugins/sfGuardPlugin-1.0.0.tgz|INFO]

If the plugin contains some web content (images, stylesheets or javascripts),
the task creates a [%name%|COMMENT] symbolic link for those assets under [web/|COMMENT].
On Windows, the task copy all the files to the [web/%name%|COMMENT] directory.
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $this->logSection('plugin', sprintf('installing plugin "%s"', $arguments['name']));
        $options['version'] = $options['release'];
        unset($options['release']);
        // license compatible?
        if (!$options['force-license']) {
            try {
                $license = $this->getPluginManager()->getPluginLicense($arguments['name'], $options);
            } catch (Exception $e) {
                throw new CommandException(sprintf('%s (use --force-license to force installation)', $e->getMessage()));
            }
            if (false !== $license) {
                $temp = trim(str_replace('license', '', strtolower($license)));
                if (null !== $license && !in_array($temp, array('mit', 'bsd', 'lgpl', 'php', 'apache'))) {
                    throw new CommandException(sprintf('The license of this plugin "%s" is not MIT like (use --force-license to force installation).', $license));
                }
            }
        }
        $this->getPluginManager()->installPlugin($arguments['name'], $options);
    }
}
class_alias(PluginInstallTask::class, 'sfPluginInstallTask', false);