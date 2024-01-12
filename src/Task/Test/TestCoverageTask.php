<?php

namespace Symfony1\Components\Task\Test;

use Symfony1\Components\Task\BaseTask;
use Symfony1\Components\Command\CommandArgument;
use Symfony1\Components\Command\CommandOption;
use Symfony1\Components\Config\Config;
use lime_harness;
use lime_coverage;
use Symfony1\Components\Util\Finder;
use Symfony1\Components\Command\CommandException;
use function count;
use function sprintf;
use function array_map;
use function is_dir;
use function file_exists;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Outputs test code coverage.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class TestCoverageTask extends BaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(new CommandArgument('test_name', CommandArgument::REQUIRED, 'A test file name or a test directory'), new CommandArgument('lib_name', CommandArgument::REQUIRED, 'A lib file name or a lib directory for wich you want to know the coverage')));
        $this->addOptions(array(new CommandOption('detailed', null, CommandOption::PARAMETER_NONE, 'Output detailed information')));
        $this->namespace = 'test';
        $this->name = 'coverage';
        $this->briefDescription = 'Outputs test code coverage';
        $this->detailedDescription = <<<'EOF'
The [test:coverage|INFO] task outputs the code coverage
given a test file or test directory
and a lib file or lib directory for which you want code
coverage:

  [./symfony test:coverage test/unit/model lib/model|INFO]

To output the lines not covered, pass the [--detailed|INFO] option:

  [./symfony test:coverage --detailed test/unit/model lib/model|INFO]
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        require_once Config::get('sf_symfony_lib_dir') . '/vendor/lime/lime.php';
        $coverage = $this->getCoverage($this->getTestHarness(array('force_colors' => isset($options['color']) && $options['color'])), $options['detailed']);
        $testFiles = $this->getFiles(Config::get('sf_root_dir') . '/' . $arguments['test_name']);
        $max = count($testFiles);
        foreach ($testFiles as $i => $file) {
            $this->logSection('coverage', sprintf('running %s (%d/%d)', $file, $i + 1, $max));
            $coverage->process($file);
        }
        $coveredFiles = $this->getFiles(Config::get('sf_root_dir') . '/' . $arguments['lib_name']);
        $coverage->output($coveredFiles);
    }
    protected function getTestHarness($harnessOptions = array())
    {
        require_once __DIR__ . '/sfLimeHarness.class.php';
        $harness = new LimeHarness($harnessOptions);
        $harness->addPlugins(array_map(array($this->configuration, 'getPluginConfiguration'), $this->configuration->getPlugins()));
        $harness->base_dir = Config::get('sf_root_dir');
        return $harness;
    }
    protected function getCoverage(lime_harness $harness, $detailed = false)
    {
        $coverage = new lime_coverage($harness);
        $coverage->verbose = $detailed;
        $coverage->base_dir = Config::get('sf_root_dir');
        return $coverage;
    }
    protected function getFiles($directory)
    {
        if (is_dir($directory)) {
            return Finder::type('file')->name('*.php')->in($directory);
        }
        if (file_exists($directory)) {
            return array($directory);
        }
        throw new CommandException(sprintf('File or directory "%s" does not exist.', $directory));
    }
}
class_alias(TestCoverageTask::class, 'sfTestCoverageTask', false);