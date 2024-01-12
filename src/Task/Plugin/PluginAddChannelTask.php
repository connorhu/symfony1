<?php

namespace Symfony1\Components\Task\Plugin;

use Symfony1\Components\Command\CommandArgument;
use function sprintf;
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
class PluginAddChannelTask extends PluginBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(new CommandArgument('name', CommandArgument::REQUIRED, 'The channel name')));
        $this->namespace = 'plugin';
        $this->name = 'add-channel';
        $this->briefDescription = 'Add a new PEAR channel';
        $this->detailedDescription = <<<'EOF'
The [plugin:add-channel|INFO] task adds a new PEAR channel:

  [./symfony plugin:add-channel symfony.plugins.pear.example.com|INFO]
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $this->logSection('plugin', sprintf('add channel "%s"', $arguments['name']));
        $this->getPluginManager()->getEnvironment()->registerChannel($arguments['name']);
    }
}
class_alias(PluginAddChannelTask::class, 'sfPluginAddChannelTask', false);