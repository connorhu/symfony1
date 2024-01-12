<?php

namespace Symfony1\Components\I18n\Extract;

use Symfony1\Components\Yaml\Yaml;
use Symfony1\Components\Config\Config;
use function is_array;
use function preg_match;
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
class I18nYamlValidateExtractor extends I18nYamlExtractor
{
    /**
     * Extract i18n strings for the given content.
     *
     * @param string $content The content
     *
     * @return array An array of i18n strings
     */
    public function extract($content)
    {
        $strings = array();
        $config = Yaml::load($content, Config::get('sf_charset', 'UTF-8'));
        // New validate.yml format
        // fields
        if (isset($config['fields'])) {
            foreach ($config['fields'] as $field => $validation) {
                foreach ($validation as $type => $parameters) {
                    if (!is_array($parameters)) {
                        continue;
                    }
                    foreach ($parameters as $key => $value) {
                        if (preg_match('/(msg|error)$/', $key)) {
                            $strings[] = $value;
                        }
                    }
                }
            }
        }
        // validators
        if (isset($config['validators'])) {
            foreach (array_keys($config['validators']) as $name) {
                if (!isset($config['validators'][$name]['param'])) {
                    continue;
                }
                foreach ($config['validators'][$name]['param'] as $key => $value) {
                    if (preg_match('/(msg|error)$/', $key)) {
                        $strings[] = $value;
                    }
                }
            }
        }
        // Old validate.yml format
        // required messages
        if (isset($config['names'])) {
            foreach ($config['names'] as $key => $value) {
                if (isset($value['required_msg'])) {
                    $strings[] = $value['required_msg'];
                }
            }
        }
        // validators
        foreach ($config as $key => $value) {
            if (isset($value['param'], $value['class'])) {
                foreach ($value['param'] as $key => $value) {
                    if (preg_match('/(msg|error)$/', $key)) {
                        $strings[] = $value;
                    }
                }
            }
        }
        return $strings;
    }
}
class_alias(I18nYamlValidateExtractor::class, 'sfI18nYamlValidateExtractor', false);