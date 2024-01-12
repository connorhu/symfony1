<?php

namespace Symfony1\Components\Task\Plugin;

use Symfony1\Components\Command\CommandArgument;
use Symfony1\Components\Command\CommandOption;
use InvalidArgumentException;
use Symfony1\Components\Util\Finder;
use Symfony1\Components\Config\Config;
use function array_diff;
use function implode;
use function array_unique;
use function array_merge;
use function array_intersect;
use function count;
use function is_dir;
use const DIRECTORY_SEPARATOR;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Publishes Web Assets for Core and third party plugins.
 *
 * @author Fabian Lange <fabian.lange@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PluginPublishAssetsTask extends PluginBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(new CommandArgument('plugins', CommandArgument::OPTIONAL | CommandArgument::IS_ARRAY, 'Publish this plugin\'s assets')));
        $this->addOptions(array(new CommandOption('core-only', '', CommandOption::PARAMETER_NONE, 'If set only core plugins will publish their assets')));
        $this->addOptions(array(new CommandOption('relative', '', CommandOption::PARAMETER_NONE, 'If set symlinks will be relative')));
        $this->namespace = 'plugin';
        $this->name = 'publish-assets';
        $this->briefDescription = 'Publishes web assets for all plugins';
        $this->detailedDescription = <<<'EOF'
The [plugin:publish-assets|INFO] task will publish web assets from all plugins.

  [./symfony plugin:publish-assets|INFO]

In fact this will send the [plugin.post_install|INFO] event to each plugin.

You can specify which plugin or plugins should install their assets by passing
those plugins' names as arguments:

  [./symfony plugin:publish-assets sfDoctrinePlugin|INFO]
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $enabledPlugins = $this->configuration->getPlugins();
        if ($diff = array_diff($arguments['plugins'], $enabledPlugins)) {
            throw new InvalidArgumentException('Plugin(s) not found: ' . implode(', ', $diff));
        }
        if ($options['core-only']) {
            $corePlugins = Finder::type('dir')->relative()->maxdepth(0)->in($this->configuration->getSymfonyLibDir() . '/plugins');
            $arguments['plugins'] = array_unique(array_merge($arguments['plugins'], array_intersect($enabledPlugins, $corePlugins)));
        } elseif (!count($arguments['plugins'])) {
            $arguments['plugins'] = $enabledPlugins;
        }
        foreach ($arguments['plugins'] as $plugin) {
            $pluginConfiguration = $this->configuration->getPluginConfiguration($plugin);
            $this->logSection('plugin', 'Configuring plugin - ' . $plugin);
            $this->installPluginAssets($plugin, $pluginConfiguration->getRootDir(), $options['relative']);
        }
    }
    /**
     * Installs web content for a plugin.
     *
     * @param string $plugin The plugin name
     * @param string $dir The plugin directory
     */
    protected function installPluginAssets($plugin, $dir, $relative)
    {
        $webDir = $dir . DIRECTORY_SEPARATOR . 'web';
        if (is_dir($webDir)) {
            if ($relative) {
                $this->getFilesystem()->relativeSymlink($webDir, Config::get('sf_web_dir') . DIRECTORY_SEPARATOR . $plugin, true);
            } else {
                $this->getFilesystem()->symlink($webDir, Config::get('sf_web_dir') . DIRECTORY_SEPARATOR . $plugin, true);
            }
        }
    }
}
class_alias(PluginPublishAssetsTask::class, 'sfPluginPublishAssetsTask', false);