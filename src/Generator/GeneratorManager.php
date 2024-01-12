<?php

namespace Symfony1\Components\Generator;

use Symfony1\Components\Config\ProjectConfiguration;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Exception\CacheException;
use function dirname;
use function is_dir;
use function umask;
use function mkdir;
use function sprintf;
use function file_put_contents;
use const DIRECTORY_SEPARATOR;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfGeneratorManager helps generate classes, views and templates for scaffolding, admin interface, ...
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class GeneratorManager
{
    protected $configuration;
    protected $basePath;
    /**
     * Class constructor.
     *
     * @param ProjectConfiguration $configuration A sfProjectConfiguration instance
     * @param string $basePath The base path for file generation
     */
    public function __construct(ProjectConfiguration $configuration, $basePath = null)
    {
        $this->configuration = $configuration;
        $this->basePath = $basePath;
    }
    /**
     * Returns the current configuration instance.
     *
     * @return ProjectConfiguration A sfProjectConfiguration instance
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
    /**
     * Gets the base path to use when generating files.
     *
     * @return string The base path
     */
    public function getBasePath()
    {
        if (null === $this->basePath) {
            // for BC
            $this->basePath = Config::get('sf_module_cache_dir');
        }
        return $this->basePath;
    }
    /**
     * Sets the base path to use when generating files.
     *
     * @param string $basePath The base path
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }
    /**
     * Saves some content.
     *
     * @param string $path The relative path
     * @param string $content The content
     *
     * @return int
     *
     * @throws CacheException
     */
    public function save($path, $content)
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . $path;
        $cacheDir = dirname($path);
        if (!is_dir($cacheDir)) {
            $current_umask = umask(00);
            if (!@mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
                throw new CacheException(sprintf('Failed to make cache directory "%s".', $cacheDir));
            }
            umask($current_umask);
        }
        if (false === ($ret = @file_put_contents($path, $content))) {
            throw new CacheException(sprintf('Failed to write cache file "%s".', $path));
        }
        return $ret;
    }
    /**
     * Generates classes and templates for a given generator class.
     *
     * @param string $generatorClass The generator class name
     * @param array $param An array of parameters
     *
     * @return string The cache for the configuration file
     */
    public function generate($generatorClass, $param)
    {
        /**
         * @var Generator $generator
         */
        $generator = new $generatorClass($this);
        return $generator->generate($param);
    }
}
class_alias(GeneratorManager::class, 'sfGeneratorManager', false);