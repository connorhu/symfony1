<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony1\Components\Util;

use Symfony\Component\Finder\Finder as SymfonyFinder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Allow to build rules to find files and directories.
 *
 * All rules may be invoked several times, except for ->in() method.
 * Some rules are cumulative (->name() for example) whereas others are destructive
 * (most recent value is used, ->maxdepth() method for example).
 *
 * All methods return the current sfFinder object to allow easy chaining:
 *
 * $files = sfFinder::type('file')->name('*.php')->in(.);
 *
 * Interface loosely based on perl File::Find::Rule module.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id$
 */
class Finder
{
    protected $type = 'file';
    protected $names = array();
    protected $prunes = array();
    protected $discards = array();
    protected $execs = array();
    protected $mindepth = 0;
    protected $sizes = array();
    protected $maxdepth = 1000000;
    protected $relative = false;
    protected $follow_link = false;
    protected $sort = false;
    protected $ignore_version_control = true;

    protected $finderInstance;

    public function __construct()
    {
        $this->finderInstance = SymfonyFinder::create();
    }

    /**
     * Sets maximum directory depth.
     *
     * Finder will descend at most $level levels of directories below the starting point.
     *
     * @param int $level
     *
     * @return sfFinder current sfFinder object
     *
     * @deprecated
     */
    public function maxdepth($level)
    {
        $this->finderInstance->depth(sprintf('< %d', $level));

        $this->maxdepth = $level;

        return $this;
    }

    /**
     * Sets minimum directory depth.
     *
     * Finder will start applying tests at level $level.
     *
     * @param int $level
     *
     * @return sfFinder current sfFinder object
     *
     * @deprecated
     */
    public function mindepth($level)
    {
        $this->finderInstance->depth(sprintf('> %d', $level));

        $this->mindepth = $level;

        return $this;
    }

    /**
     * @see SymfonyFinder::directories()
     * @see SymfonyFinder::files()
     * @deprecated
     */
    public function get_type()
    {
        return $this->type;
    }

    /**
     * Sets the type of elements to returns.
     *
     * @param string $name directory or file or any (for both file and directory)
     *
     * @return sfFinder new sfFinder object
     *
     * @deprecated
     */
    public static function type($name)
    {
        $finder = new self();

        return $finder->setType($name);
    }

    /**
     * Sets the type of elements to returns.
     *
     * @param string $name directory or file or any (for both file and directory)
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function setType($name)
    {
        $name = strtolower($name);

        if ('dir' === substr($name, 0, 3)) {
            $errorMessage = sprintf('Using the %s::%s() method is deprecated since symfony1 version 1.6, use %s::%s() instead.', Finder::class, __METHOD__, SymfonyFinder::class, 'directories');
            @trigger_error($errorMessage, \E_USER_DEPRECATED);

            $this->finderInstance->directories();
            $this->type = 'directory';

            return $this;
        }
        if ('any' === $name) {
            $this->type = 'any';

            return $this;
        }

        $errorMessage = sprintf('Using the %s::%s() method is deprecated since symfony1 version 1.6, use %s::%s() instead.', Finder::class, __METHOD__, SymfonyFinder::class, 'files');
        @trigger_error($errorMessage, \E_USER_DEPRECATED);

        $this->finderInstance->files();
        $this->type = 'file';

        return $this;
    }

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $finder->name('*.php')
     * $finder->name('/\.php$/') // same as above
     * $finder->name('test.php')
     *
     * @param  list   a list of patterns, globs or strings
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function name()
    {
        $args = func_get_args();

        $this->finderInstance->name($args);

        return $this;
    }

    /**
     * Adds rules that files must not match.
     *
     * @param  list   a list of patterns, globs or strings
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function not_name()
    {
        $args = func_get_args();

        $this->finderInstance->notName($args);

        return $this;
    }

    /**
     * Adds tests for file sizes.
     *
     * $finder->size('> 10K');
     * $finder->size('<= 1Ki');
     * $finder->size(4);
     *
     * @param  list   a list of comparison strings
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function size()
    {
        $args = func_get_args();

        $this->finderInstance->size($args);

        return $this;
    }

    /**
     * Traverses no further.
     *
     * @param  list   a list of patterns, globs to match
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function prune()
    {
        $args = func_get_args();
        $this->prunes = array_merge($this->prunes, $this->args_to_array($args));

        return $this;
    }

    /**
     * Discards elements that matches.
     *
     * @param  list   a list of patterns, globs to match
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function discard()
    {
        $args = func_get_args();
        $this->discards = array_merge($this->discards, $this->args_to_array($args));

        return $this;
    }

    /**
     * Ignores version control directories.
     *
     * Currently supports Subversion, CVS, DARCS, Gnu Arch, Monotone, Bazaar-NG, GIT, Mercurial
     *
     * @param bool $ignore falase when version control directories shall be included (default is true)
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function ignore_version_control($ignore = true)
    {
        $this->finderInstance->ignoreVCS($ignore);

        return $this;
    }

    /**
     * Returns files and directories ordered by name.
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function sort_by_name()
    {
        $this->finderInstance->sortByName();

        return $this;
    }

    /**
     * Returns files and directories ordered by type (directories before files), then by name.
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function sort_by_type()
    {
        $this->finderInstance->sortByType();

        return $this;
    }

    /**
     * Executes function or method for each element.
     *
     * Element match if functino or method returns true.
     *
     * $finder->exec('myfunction');
     * $finder->exec(array($object, 'mymethod'));
     *
     * @param  mixed  function or method to call
     *
     * @return sfFinder Current object
     */
    public function exec()
    {
        $args = func_get_args();
        $numargs = count($args);
        for ($i = 0; $i < $numargs; ++$i) {
            if (is_array($args[$i]) && !method_exists($args[$i][0], $args[$i][1])) {
                throw new sfException(sprintf('method "%s" does not exist for object "%s".', $args[$i][1], $args[$i][0]));
            }
            if (!is_array($args[$i]) && !function_exists($args[$i])) {
                throw new sfException(sprintf('function "%s" does not exist.', $args[$i]));
            }

            $this->execs[] = $args[$i];
        }

        return $this;
    }

    /**
     * Returns relative paths for all files and directories.
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function relative()
    {
        $this->relative = true;

        return $this;
    }

    /**
     * Symlink following.
     *
     * @return sfFinder Current object
     *
     * @deprecated
     */
    public function follow_link()
    {
        $this->finderInstance->followLinks();

        return $this;
    }

    /**
     * Searches files and directories which match defined rules.
     *
     * @return array list of files and directories
     *
     * @deprecated
     */
    public function in()
    {
        $args = func_get_args();
        if (count($args) === 1) {
            $args = current($args);
        }

        $this->finderInstance->in($args);

        return array_map(function (SplFileInfo $spl) {
            if ($this->relative) {
                $path = $spl->getRelativePath();
                return empty($path) ? $spl->getFilename() : $path.DIRECTORY_SEPARATOR.$spl->getFilename();
            }

            return $spl->getRealPath();

        }, iterator_to_array($this->finderInstance->getIterator()));
    }

    public static function isPathAbsolute($path)
    {
        if ('/' === $path[0] || '\\' === $path[0]
            || (
                strlen($path) > 3 && ctype_alpha($path[0])
             && ':' === $path[1]
             && ('\\' === $path[2] || '/' === $path[2])
            )
        ) {
            return true;
        }

        return false;
    }
}
