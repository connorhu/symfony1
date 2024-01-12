<?php

namespace Symfony1\Components\I18n\Extract;

use Symfony1\Components\Util\Finder;
use Symfony1\Components\Config\Config;
use function array_unique;
use function array_merge;
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
class I18nApplicationExtract extends I18nExtract
{
    protected $extractObjects = array();
    /**
     * Configures the current extract object.
     */
    public function configure()
    {
        $this->extractObjects = array();
        // Modules
        $moduleNames = Finder::type('dir')->maxdepth(0)->relative()->in(Config::get('sf_app_module_dir'));
        foreach ($moduleNames as $moduleName) {
            $this->extractObjects[] = new I18nModuleExtract($this->i18n, $this->culture, array('module' => $moduleName));
        }
    }
    /**
     * Extracts i18n strings.
     *
     * This class must be implemented by subclasses.
     */
    public function extract()
    {
        foreach ($this->extractObjects as $extractObject) {
            $extractObject->extract();
        }
        // Add global templates
        $this->extractFromPhpFiles(Config::get('sf_app_template_dir'));
        // Add global librairies
        $this->extractFromPhpFiles(Config::get('sf_app_lib_dir'));
    }
    /**
     * Gets the current i18n strings.
     */
    public function getCurrentMessages()
    {
        return array_unique(array_merge($this->currentMessages, $this->aggregateMessages('getCurrentMessages')));
    }
    /**
     * Gets all i18n strings seen during the extraction process.
     */
    public function getAllSeenMessages()
    {
        return array_unique(array_merge($this->allSeenMessages, $this->aggregateMessages('getAllSeenMessages')));
    }
    protected function aggregateMessages($method)
    {
        $messages = array();
        foreach ($this->extractObjects as $extractObject) {
            $messages = array_merge($messages, $extractObject->{$method}());
        }
        return array_unique($messages);
    }
}
class_alias(I18nApplicationExtract::class, 'sfI18nApplicationExtract', false);