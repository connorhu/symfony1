<?php

namespace Symfony1\Components\I18n\Extract;

use Symfony1\Components\Yaml\Yaml;
use Symfony1\Components\Config\Config;
use function array_keys;
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
class I18nYamlGeneratorExtractor extends I18nYamlExtractor
{
    protected $strings = array();
    /**
     * Extract i18n strings for the given content.
     *
     * @param string $content The content
     *
     * @return array An array of i18n strings
     */
    public function extract($content)
    {
        $this->strings = array();
        $config = Yaml::load($content, Config::get('sf_charset', 'UTF-8'));
        if (!isset($config['generator']['param']['config'])) {
            return array();
        }
        $params = $config['generator']['param']['config'];
        // titles
        foreach (array('list', 'edit', 'new') as $section) {
            if (isset($params[$section]['title'])) {
                $this->strings[] = $params[$section]['title'];
            }
        }
        // names and help messages
        if (isset($params['fields'])) {
            $this->getFromFields($params['fields']);
        }
        if (isset($params['list']['fields'])) {
            $this->getFromFields($params['list']['fields']);
        }
        if (isset($params['edit']['fields'])) {
            $this->getFromFields($params['edit']['fields']);
        }
        if (isset($params['new']['fields'])) {
            $this->getFromFields($params['new']['fields']);
        }
        // form categories
        foreach (array('edit', 'new') as $section) {
            if (isset($params[$section]['display']) && !isset($params[$section]['display'][0])) {
                foreach (array_keys($params[$section]['display']) as $string) {
                    if ('NONE' != $string) {
                        $this->strings[] = $string;
                    }
                }
            }
        }
        return $this->strings;
    }
    protected function getFromFields($fields)
    {
        foreach ($fields as $field => $options) {
            if (isset($options['name'])) {
                $this->strings[] = $options['name'];
            }
            if (isset($options['label'])) {
                $this->strings[] = $options['label'];
            }
            if (isset($options['help'])) {
                $this->strings[] = $options['help'];
            }
        }
    }
}
class_alias(I18nYamlGeneratorExtractor::class, 'sfI18nYamlGeneratorExtractor', false);