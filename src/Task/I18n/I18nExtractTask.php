<?php

namespace Symfony1\Components\Task\I18n;

use Symfony1\Components\Task\BaseTask;
use Symfony1\Components\Config\FactoryConfigHandler;
use Symfony1\Components\I18n\Extract\I18nApplicationExtract;
use Symfony1\Components\Cache\NoCache;
use Symfony1\Components\Command\CommandArgument;
use Symfony1\Components\Command\CommandOption;
use function sprintf;
use function count;
/*
 * Current known limitations:
 *   - Can only works with the default "messages" catalogue
 *   - For file backends (XLIFF and gettext), it only saves/deletes strings in the "most global" file
 */
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Extracts i18n strings from php files.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class I18nExtractTask extends BaseTask
{
    /**
     * @see sfTask
     */
    public function execute($arguments = array(), $options = array())
    {
        $this->logSection('i18n', sprintf('extracting i18n strings for the "%s" application', $arguments['application']));
        // get i18n configuration from factories.yml
        $config = FactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
        $class = $config['i18n']['class'];
        $params = $config['i18n']['param'];
        unset($params['cache']);
        $extract = new I18nApplicationExtract(new $class($this->configuration, new NoCache(), $params), $arguments['culture']);
        $extract->extract();
        $this->logSection('i18n', sprintf('found "%d" new i18n strings', count($extract->getNewMessages())));
        $this->logSection('i18n', sprintf('found "%d" old i18n strings', count($extract->getOldMessages())));
        if ($options['display-new']) {
            $this->logSection('i18n', sprintf('display "%d" new i18n strings', count($extract->getOldMessages())));
            foreach ($extract->getNewMessages() as $message) {
                $this->log('               ' . $message . "\n");
            }
        }
        if ($options['auto-save']) {
            $this->logSection('i18n', 'saving new i18n strings');
            $extract->saveNewMessages();
        }
        if ($options['display-old']) {
            $this->logSection('i18n', sprintf('display "%d" old i18n strings', count($extract->getOldMessages())));
            foreach ($extract->getOldMessages() as $message) {
                $this->log('               ' . $message . "\n");
            }
        }
        if ($options['auto-delete']) {
            $this->logSection('i18n', 'deleting old i18n strings');
            $extract->deleteOldMessages();
        }
    }
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(new CommandArgument('application', CommandArgument::REQUIRED, 'The application name'), new CommandArgument('culture', CommandArgument::REQUIRED, 'The target culture')));
        $this->addOptions(array(new CommandOption('env', null, CommandOption::PARAMETER_OPTIONAL, 'The environment', 'dev'), new CommandOption('display-new', null, CommandOption::PARAMETER_NONE, 'Output all new found strings'), new CommandOption('display-old', null, CommandOption::PARAMETER_NONE, 'Output all old strings'), new CommandOption('auto-save', null, CommandOption::PARAMETER_NONE, 'Save the new strings'), new CommandOption('auto-delete', null, CommandOption::PARAMETER_NONE, 'Delete old strings')));
        $this->namespace = 'i18n';
        $this->name = 'extract';
        $this->briefDescription = 'Extracts i18n strings from php files';
        $this->detailedDescription = <<<'EOF'
The [i18n:extract|INFO] task extracts i18n strings from your project files
for the given application and target culture:

  [./symfony i18n:extract frontend fr|INFO]

By default, the task only displays the number of new and old strings
it found in the current project.

You can specify project environment by setting option:

  [./symfony i18n:extract --env=ENVIRONMENT|INFO]

If you want to display the new strings, use the [--display-new|COMMENT] option:

  [./symfony i18n:extract --display-new frontend fr|INFO]

To save them in the i18n message catalogue, use the [--auto-save|COMMENT] option:

  [./symfony i18n:extract --auto-save frontend fr|INFO]

If you want to display strings that are present in the i18n messages
catalogue but are not found in the application, use the
[--display-old|COMMENT] option:

  [./symfony i18n:extract --display-old frontend fr|INFO]

To automatically delete old strings, use the [--auto-delete|COMMENT] but
be careful, especially if you have translations for plugins as they will
appear as old strings but they are not:

  [./symfony i18n:extract --auto-delete frontend fr|INFO]
EOF;
    }
}
class_alias(I18nExtractTask::class, 'sfI18nExtractTask', false);