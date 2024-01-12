<?php

namespace Symfony1\Components\I18n\Extract;

use Symfony1\Components\Exception\Exception;
use Symfony1\Components\Config\Config;
use function file_exists;
use function file_get_contents;
use function glob;
use function is_array;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class I18nModuleExtract extends I18nExtract
{
    protected $module = '';
    /**
     * Configures the current extract object.
     */
    public function configure()
    {
        if (!isset($this->parameters['module'])) {
            throw new Exception('You must give a "module" parameter when extracting for a module.');
        }
        $this->module = $this->parameters['module'];
        $options = $this->i18n->getOptions();
        $dirs = $this->i18n->isMessageSourceFileBased($options['source']) ? $this->i18n->getConfiguration()->getI18NDirs($this->module) : null;
        $this->i18n->setMessageSource($dirs, $this->culture);
    }
    /**
     * Extracts i18n strings.
     *
     * This class must be implemented by subclasses.
     */
    public function extract()
    {
        // Extract from PHP files to find __() calls in actions/ lib/ and templates/ directories
        $moduleDir = Config::get('sf_app_module_dir') . '/' . $this->module;
        $this->extractFromPhpFiles(array($moduleDir . '/actions', $moduleDir . '/lib', $moduleDir . '/templates'));
        // Extract from generator.yml files
        $generator = $moduleDir . '/config/generator.yml';
        if (file_exists($generator)) {
            $yamlExtractor = new I18nYamlGeneratorExtractor();
            $this->updateMessages($yamlExtractor->extract(file_get_contents($generator)));
        }
        // Extract from validate/*.yml files
        $validateFiles = glob($moduleDir . '/validate/*.yml');
        if (is_array($validateFiles)) {
            foreach ($validateFiles as $validateFile) {
                $yamlExtractor = new I18nYamlValidateExtractor();
                $this->updateMessages($yamlExtractor->extract(file_get_contents($validateFile)));
            }
        }
    }
}
class_alias(I18nModuleExtract::class, 'sfI18nModuleExtract', false);