<?php

namespace Symfony1\Components\Task\Generator;

use Symfony1\Components\Command\CommandManager;
use Symfony1\Components\Command\CommandArgument;
use Symfony1\Components\Command\CommandOption;
use Symfony1\Components\Command\CommandException;
use InvalidArgumentException;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Task\Project\ProjectPermissionsTask;
use function file_exists;
use function sprintf;
use function getcwd;
use function in_array;
use function strtolower;
use function ucfirst;
use function strpos;
use function str_replace;
use function var_export;
use function preg_match;
use function ini_get;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Generates a new project.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class GenerateProjectTask extends GeneratorBaseTask
{
    /**
     * @see sfTask
     */
    protected function doRun(CommandManager $commandManager, $options)
    {
        $this->process($commandManager, $options);
        return $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());
    }
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(new CommandArgument('name', CommandArgument::REQUIRED, 'The project name'), new CommandArgument('author', CommandArgument::OPTIONAL, 'The project author', 'Your name here')));
        $this->addOptions(array(new CommandOption('orm', null, CommandOption::PARAMETER_REQUIRED, 'The ORM to use by default', 'Doctrine'), new CommandOption('installer', null, CommandOption::PARAMETER_REQUIRED, 'An installer script to execute', null)));
        $this->namespace = 'generate';
        $this->name = 'project';
        $this->briefDescription = 'Generates a new project';
        $this->detailedDescription = <<<'EOF'
The [generate:project|INFO] task creates the basic directory structure
for a new project in the current directory:

  [./symfony generate:project blog|INFO]

If the current directory already contains a symfony project,
it throws a [sfCommandException|COMMENT].

By default, the task configures Doctrine as the ORM.

If you don't want to use an ORM, pass [none|COMMENT] to [--orm|COMMENT] option:

  [./symfony generate:project blog --orm=none|INFO]

You can also pass the [--installer|COMMENT] option to further customize the
project:

  [./symfony generate:project blog --installer=./installer.php|INFO]

You can optionally include a second [author|COMMENT] argument to specify what name to
use as author when symfony generates new classes:

  [./symfony generate:project blog "Jack Doe"|INFO]
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        if (file_exists('symfony')) {
            throw new CommandException(sprintf('A symfony project already exists in this directory (%s).', getcwd()));
        }
        if (!in_array(strtolower($options['orm']), array('doctrine', 'none'))) {
            throw new InvalidArgumentException(sprintf('Invalid ORM name "%s".', $options['orm']));
        }
        if ($options['installer'] && $this->commandApplication && !file_exists($options['installer'])) {
            throw new InvalidArgumentException(sprintf('The installer "%s" does not exist.', $options['installer']));
        }
        // clean orm option
        $options['orm'] = ucfirst(strtolower($options['orm']));
        $this->arguments = $arguments;
        $this->options = $options;
        // create basic project structure
        $this->installDir(__DIR__ . '/skeleton/project');
        // update ProjectConfiguration class (use a relative path when the symfony core is nested within the project)
        $symfonyCoreAutoload = 0 === strpos(Config::get('sf_symfony_lib_dir'), Config::get('sf_root_dir')) ? sprintf('__DIR__.\'/..%s/autoload/sfCoreAutoload.class.php\'', str_replace(Config::get('sf_root_dir'), '', Config::get('sf_symfony_lib_dir'))) : var_export(Config::get('sf_symfony_lib_dir') . '/autoload/sfCoreAutoload.class.php', true);
        $this->replaceTokens(array(Config::get('sf_config_dir')), array('SYMFONY_CORE_AUTOLOAD' => str_replace('\\', '/', $symfonyCoreAutoload)));
        $this->tokens = array('ORM' => $this->options['orm'], 'PROJECT_NAME' => $this->arguments['name'], 'AUTHOR_NAME' => $this->arguments['author'], 'PROJECT_DIR' => Config::get('sf_root_dir'));
        $this->replaceTokens();
        // execute the choosen ORM installer script
        if ('Doctrine' === $options['orm']) {
            include __DIR__ . '/../../plugins/sf' . $options['orm'] . 'Plugin/config/installer.php';
        }
        // execute a custom installer
        if ($options['installer'] && $this->commandApplication) {
            if ($this->canRunInstaller($options['installer'])) {
                $this->reloadTasks();
                include $options['installer'];
            }
        }
        // fix permission for common directories
        $fixPerms = new ProjectPermissionsTask($this->dispatcher, $this->formatter);
        $fixPerms->setCommandApplication($this->commandApplication);
        $fixPerms->setConfiguration($this->configuration);
        $fixPerms->run();
        $this->replaceTokens();
    }
    protected function canRunInstaller($installer)
    {
        if (preg_match('#^(https?|ftps?)://#', $installer)) {
            if (false === ini_get('allow_url_fopen')) {
                $this->logSection('generate', sprintf('Cannot run remote installer "%s" because "allow_url_fopen" is off', $installer));
            }
            if (false === ini_get('allow_url_include')) {
                $this->logSection('generate', sprintf('Cannot run remote installer "%s" because "allow_url_include" is off', $installer));
            }
            return ini_get('allow_url_fopen') && ini_get('allow_url_include');
        }
        return true;
    }
}
class_alias(GenerateProjectTask::class, 'sfGenerateProjectTask', false);