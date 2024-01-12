<?php

namespace Symfony1\Components\Task\Generator;

use Symfony1\Components\Command\CommandArgument;
use Symfony1\Components\Command\CommandException;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Util\Finder;
use function preg_match;
use function sprintf;
use function is_dir;
use function parse_ini_file;
use function is_readable;
use const DIRECTORY_SEPARATOR;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Generates a new module.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class GenerateModuleTask extends GeneratorBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(new CommandArgument('application', CommandArgument::REQUIRED, 'The application name'), new CommandArgument('module', CommandArgument::REQUIRED, 'The module name')));
        $this->namespace = 'generate';
        $this->name = 'module';
        $this->briefDescription = 'Generates a new module';
        $this->detailedDescription = <<<'EOF'
The [generate:module|INFO] task creates the basic directory structure
for a new module in an existing application:

  [./symfony generate:module frontend article|INFO]

The task can also change the author name found in the [actions.class.php|COMMENT]
if you have configure it in [config/properties.ini|COMMENT]:

  [[symfony]
    name=blog
    author=Fabien Potencier <fabien.potencier@sensio.com>|INFO]

You can customize the default skeleton used by the task by creating a
[%sf_data_dir%/skeleton/module|COMMENT] directory.

The task also creates a functional test stub named
[%sf_test_dir%/functional/%application%/%module%ActionsTest.class.php|COMMENT]
that does not pass by default.

If a module with the same name already exists in the application,
it throws a [sfCommandException|COMMENT].
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $app = $arguments['application'];
        $module = $arguments['module'];
        // Validate the module name
        if (!preg_match('/^[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*$/', $module)) {
            throw new CommandException(sprintf('The module name "%s" is invalid.', $module));
        }
        $moduleDir = Config::get('sf_app_module_dir') . '/' . $module;
        if (is_dir($moduleDir)) {
            throw new CommandException(sprintf('The module "%s" already exists in the "%s" application.', $moduleDir, $app));
        }
        $properties = parse_ini_file(Config::get('sf_config_dir') . '/properties.ini', true);
        $constants = array('PROJECT_NAME' => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony', 'APP_NAME' => $app, 'MODULE_NAME' => $module, 'AUTHOR_NAME' => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here');
        if (is_readable(Config::get('sf_data_dir') . '/skeleton/module')) {
            $skeletonDir = Config::get('sf_data_dir') . '/skeleton/module';
        } else {
            $skeletonDir = __DIR__ . '/skeleton/module';
        }
        // create basic application structure
        $finder = Finder::type('any')->discard('.sf');
        $this->getFilesystem()->mirror($skeletonDir . '/module', $moduleDir, $finder);
        // create basic test
        $this->getFilesystem()->copy($skeletonDir . '/test/actionsTest.php', Config::get('sf_test_dir') . '/functional/' . $app . '/' . $module . 'ActionsTest.php');
        // customize test file
        $this->getFilesystem()->replaceTokens(Config::get('sf_test_dir') . '/functional/' . $app . DIRECTORY_SEPARATOR . $module . 'ActionsTest.php', '##', '##', $constants);
        // customize php and yml files
        $finder = Finder::type('file')->name('*.php', '*.yml');
        $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '##', '##', $constants);
    }
}
class_alias(GenerateModuleTask::class, 'sfGenerateModuleTask', false);