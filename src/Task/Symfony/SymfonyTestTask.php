<?php

namespace Symfony1\Components\Task\Symfony;

use Symfony1\Components\Task\Task;
use Symfony1\Components\Command\CommandOption;
use sfCoreAutoload;
use Symfony1\Components\Util\Finder;
use Symfony1\Components\Util\Toolkit;
use function glob;
use function sys_get_temp_dir;
use function unlink;
use function sprintf;
use function md5;
use function file_exists;
use function unserialize;
use function file_get_contents;
use function realpath;
use function array_merge;
use function file_put_contents;
use function serialize;
use const DIRECTORY_SEPARATOR;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Launches the symfony test suite.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class SymfonyTestTask extends Task
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addOptions(array(new CommandOption('update-autoloader', 'u', CommandOption::PARAMETER_NONE, 'Update the sfCoreAutoload class'), new CommandOption('only-failed', 'f', CommandOption::PARAMETER_NONE, 'Only run tests that failed last time'), new CommandOption('xml', null, CommandOption::PARAMETER_REQUIRED, 'The file name for the JUnit compatible XML log file'), new CommandOption('rebuild-all', null, CommandOption::PARAMETER_NONE, 'Rebuild all generated fixture files')));
        $this->namespace = 'symfony';
        $this->name = 'test';
        $this->briefDescription = 'Launches the symfony test suite';
        $this->detailedDescription = <<<EOF
The [{$this->getFullName()}|INFO] task launches the symfony test suite:

  [./symfony {$this->getFullName()}|INFO]
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        require_once __DIR__ . '/../../vendor/lime/lime.php';
        require_once __DIR__ . '/lime_symfony.php';
        // cleanup
        require_once __DIR__ . '/../../util/sfToolkit.class.php';
        if ($files = glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . '/sf_autoload_unit_*')) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
        // update sfCoreAutoload
        if ($options['update-autoloader']) {
            require_once __DIR__ . '/../../autoload/sfCoreAutoload.class.php';
            sfCoreAutoload::make();
        }
        $status = false;
        $statusFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . sprintf('/.test_symfony_%s_status', md5(__DIR__));
        if ($options['only-failed']) {
            if (file_exists($statusFile)) {
                $status = unserialize(file_get_contents($statusFile));
            }
        }
        $h = new lime_symfony(array('force_colors' => $options['color'], 'verbose' => $options['trace']));
        $h->base_dir = realpath(__DIR__ . '/../../../test');
        // remove generated files
        if ($options['rebuild-all']) {
            $finder = Finder::type('dir')->name(array('base', 'om', 'map'));
            foreach ($finder->in(glob($h->base_dir . '/../lib/plugins/*/test/functional/fixtures/lib')) as $dir) {
                Toolkit::clearDirectory($dir);
            }
        }
        if ($status) {
            foreach ($status as $file) {
                $h->register($file);
            }
        } else {
            $h->register(Finder::type('file')->prune('fixtures')->name('*Test.php')->in(array_merge(
                // unit tests
                array($h->base_dir . '/unit'),
                glob($h->base_dir . '/../lib/plugins/*/test/unit'),
                // functional tests
                array($h->base_dir . '/functional'),
                glob($h->base_dir . '/../lib/plugins/*/test/functional'),
                // other tests
                array($h->base_dir . '/other')
            )));
        }
        $ret = $h->run() ? 0 : 1;
        file_put_contents($statusFile, serialize($h->get_failed_files()));
        if ($options['xml']) {
            file_put_contents($options['xml'], $h->to_xml());
        }
        return $ret;
    }
}
class_alias(SymfonyTestTask::class, 'sfSymfonyTestTask', false);