<?php

namespace Symfony1\Components\Task\Project;

use Symfony1\Components\Task\BaseTask;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Util\Finder;
use Symfony1\Components\Debug\Debug;
use function file_exists;
use function count;
use function array_merge;
use function array_map;
use function is_array;
use function set_error_handler;
use function restore_error_handler;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Fixes symfony directory permissions.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class ProjectPermissionsTask extends BaseTask
{
    protected $current;
    protected $failed = array();
    /**
     * Captures those chmod commands that fail.
     *
     * @see http://www.php.net/set_error_handler
     *
     * @param (mixed | null) $context
     */
    public function handleError($no, $string, $file, $line, $context = null)
    {
        $this->failed[] = $this->current;
    }
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->namespace = 'project';
        $this->name = 'permissions';
        $this->briefDescription = 'Fixes symfony directory permissions';
        $this->detailedDescription = <<<'EOF'
The [project:permissions|INFO] task fixes directory permissions:

  [./symfony project:permissions|INFO]
EOF;
    }
    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        if (file_exists(Config::get('sf_upload_dir'))) {
            $this->chmod(Config::get('sf_upload_dir'), 0777);
        }
        $this->chmod(Config::get('sf_cache_dir'), 0777);
        $this->chmod(Config::get('sf_log_dir'), 0777);
        $this->chmod(Config::get('sf_root_dir') . '/symfony', 0777);
        $dirs = array(Config::get('sf_cache_dir'), Config::get('sf_log_dir'), Config::get('sf_upload_dir'));
        $dirFinder = Finder::type('dir');
        $fileFinder = Finder::type('file');
        foreach ($dirs as $dir) {
            $this->chmod($dirFinder->in($dir), 0777);
            $this->chmod($fileFinder->in($dir), 0666);
        }
        // note those files that failed
        if (count($this->failed)) {
            $this->logBlock(array_merge(array('Permissions on the following file(s) could not be fixed:', ''), array_map(function ($f) {
                return ' - ' . Debug::shortenFilePath($f);
            }, $this->failed)), 'ERROR_LARGE');
        }
    }
    /**
     * Chmod and capture any failures.
     *
     * @param string $file
     * @param int $mode
     * @param int $umask
     *
     * @see sfFilesystem
     */
    protected function chmod($file, $mode, $umask = 00)
    {
        if (is_array($file)) {
            foreach ($file as $f) {
                $this->chmod($f, $mode, $umask);
            }
        } else {
            set_error_handler(array($this, 'handleError'));
            $this->current = $file;
            @$this->getFilesystem()->chmod($file, $mode, $umask);
            $this->current = null;
            restore_error_handler();
        }
    }
}
class_alias(ProjectPermissionsTask::class, 'sfProjectPermissionsTask', false);