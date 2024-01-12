<?php

namespace Symfony1\Components\Task\Project\Validation;

use Symfony1\Components\Task\BaseTask;
use Symfony1\Components\Exception\Exception;
use Symfony1\Components\Util\Finder;
use Symfony1\Components\Config\Config;
use function array_merge;
use function glob;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Abstract class for validation classes.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
abstract class Validation extends BaseTask
{
    protected $task;
    /**
     * Validates the current project.
     */
    public abstract function validate();
    public abstract function getHeader();
    public function execute($arguments = array(), $options = array())
    {
        throw new Exception('You can\'t execute this task.');
    }
    /**
     * Returns a finder that exclude upgrade scripts from being upgraded!
     *
     * @param string $type String directory or file or any (for both file and directory)
     *
     * @return Finder A sfFinder instance
     */
    protected function getFinder($type)
    {
        return Finder::type($type)->prune('symfony')->discard('symfony');
    }
    /**
     * Returns all project directories where you can put PHP classes.
     */
    protected function getProjectClassDirectories()
    {
        return array_merge($this->getProjectLibDirectories(), $this->getProjectActionDirectories());
    }
    /**
     * Returns all project directories where you can put templates.
     */
    protected function getProjectTemplateDirectories()
    {
        return array_merge(glob(Config::get('sf_apps_dir') . '/*/modules/*/templates'), glob(Config::get('sf_apps_dir') . '/*/templates'));
    }
    /**
     * Returns all project directories where you can put actions and components.
     */
    protected function getProjectActionDirectories()
    {
        return glob(Config::get('sf_apps_dir') . '/*/modules/*/actions');
    }
    /**
     * Returns all project lib directories.
     *
     * @param string $subdirectory A subdirectory within lib (i.e. "/form")
     */
    protected function getProjectLibDirectories($subdirectory = null)
    {
        return array_merge(glob(Config::get('sf_apps_dir') . '/*/modules/*/lib' . $subdirectory), glob(Config::get('sf_apps_dir') . '/*/lib' . $subdirectory), array(Config::get('sf_apps_dir') . '/lib' . $subdirectory, Config::get('sf_lib_dir') . $subdirectory));
    }
    /**
     * Returns all project config directories.
     */
    protected function getProjectConfigDirectories()
    {
        return array_merge(glob(Config::get('sf_apps_dir') . '/*/modules/*/config'), glob(Config::get('sf_apps_dir') . '/*/config'), glob(Config::get('sf_config_dir')));
    }
    /**
     * Returns all application names.
     *
     * @return array An array of application names
     */
    protected function getApplications()
    {
        return Finder::type('dir')->maxdepth(0)->relative()->in(Config::get('sf_apps_dir'));
    }
}
class_alias(Validation::class, 'sfValidation', false);