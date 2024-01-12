<?php

namespace Symfony1\Components\Task\Test;

use Symfony1\Components\Command\CommandArgument;
use Symfony1\Components\Command\CommandOption;
use Symfony1\Components\Util\Finder;
use Symfony1\Components\Config\Config;
use function count;
use function basename;
use function array_merge;
use function dirname;
use function array_map;
use function file_put_contents;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Launches functional tests.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class TestFunctionalTask extends TestBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(new CommandArgument('application', CommandArgument::REQUIRED, 'The application name'), new CommandArgument('controller', CommandArgument::OPTIONAL | CommandArgument::IS_ARRAY, 'The controller name')));
        $this->addOptions(array(new CommandOption('xml', null, CommandOption::PARAMETER_REQUIRED, 'The file name for the JUnit compatible XML log file')));
        $this->namespace = 'test';
        $this->name = 'functional';
        $this->briefDescription = 'Launches functional tests';
        $this->detailedDescription = <<<'EOF'
The [test:functional|INFO] task launches functional tests for a
given application:

  [./symfony test:functional frontend|INFO]

The task launches all tests found in [test/functional/%application%|COMMENT].

If some tests fail, you can use the [--trace|COMMENT] option to have more
information about the failures:

  [./symfony test:functional frontend -t|INFO]

You can launch all functional tests for a specific controller by
giving a controller name:

  [./symfony test:functional frontend article|INFO]

You can also launch all functional tests for several controllers:

  [./symfony test:functional frontend article comment|INFO]

The task can output a JUnit compatible XML log file with the [--xml|COMMENT]
options:

  [./symfony test:functional --xml=log.xml|INFO]
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $app = $arguments['application'];
        if (count($arguments['controller'])) {
            $files = array();
            foreach ($arguments['controller'] as $controller) {
                $finder = Finder::type('file')->follow_link()->name(basename($controller) . 'Test.php');
                $files = array_merge($files, $finder->in(Config::get('sf_test_dir') . '/functional/' . $app . '/' . dirname($controller)));
            }
            if ($allFiles = $this->filterTestFiles($files, $arguments, $options)) {
                foreach ($allFiles as $file) {
                    include $file;
                }
            } else {
                $this->logSection('functional', 'no controller found', null, 'ERROR');
            }
        } else {
            require_once __DIR__ . '/sfLimeHarness.class.php';
            $h = new LimeHarness(array('force_colors' => isset($options['color']) && $options['color'], 'verbose' => isset($options['trace']) && $options['trace']));
            $h->addPlugins(array_map(array($this->configuration, 'getPluginConfiguration'), $this->configuration->getPlugins()));
            $h->base_dir = Config::get('sf_test_dir') . '/functional/' . $app;
            // filter and register functional tests
            $finder = Finder::type('file')->follow_link()->name('*Test.php');
            $h->register($this->filterTestFiles($finder->in($h->base_dir), $arguments, $options));
            $ret = $h->run() ? 0 : 1;
            if ($options['xml']) {
                file_put_contents($options['xml'], $h->to_xml());
            }
            return $ret;
        }
    }
}
class_alias(TestFunctionalTask::class, 'sfTestFunctionalTask', false);