<?php

namespace Symfony1\Components\Debug;

use Symfony1\Components\Config\Config;
use Symfony1\Components\Util\Context;
use Symfony1\Components\Yaml\Yaml;
use function extension_loaded;
use function function_exists;
use function ini_get;
use function ucfirst;
use function strtolower;
use function htmlspecialchars;
use const ENT_QUOTES;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfWebDebugPanelConfig adds a panel to the web debug toolbar with the current configuration.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class WebDebugPanelConfig extends WebDebugPanel
{
    public function getTitle()
    {
        return '<img src="' . $this->webDebug->getOption('image_root_path') . '/config.png" alt="Config" /> config';
    }
    public function getPanelTitle()
    {
        return 'Configuration';
    }
    public function getPanelContent()
    {
        $config = array('debug' => Config::get('sf_debug') ? 'on' : 'off', 'xdebug' => extension_loaded('xdebug') ? 'on' : 'off', 'logging' => Config::get('sf_logging_enabled') ? 'on' : 'off', 'cache' => Config::get('sf_cache') ? 'on' : 'off', 'compression' => Config::get('sf_compressed') ? 'on' : 'off', 'tokenizer' => function_exists('token_get_all') ? 'on' : 'off', 'eaccelerator' => extension_loaded('eaccelerator') && ini_get('eaccelerator.enable') ? 'on' : 'off', 'apc' => extension_loaded('apc') && ini_get('apc.enabled') ? 'on' : 'off', 'xcache' => extension_loaded('xcache') && ini_get('xcache.cacher') ? 'on' : 'off');
        $html = '<ul id="sfWebDebugConfigSummary">';
        foreach ($config as $key => $value) {
            $html .= '<li class="is' . $value . ('xcache' == $key ? ' last' : '') . '">' . $key . '</li>';
        }
        $html .= '</ul>';
        $context = Context::getInstance();
        $html .= $this->formatArrayAsHtml('request', Debug::requestAsArray($context->getRequest()));
        $html .= $this->formatArrayAsHtml('response', Debug::responseAsArray($context->getResponse()));
        $html .= $this->formatArrayAsHtml('user', Debug::userAsArray($context->getUser()));
        $html .= $this->formatArrayAsHtml('settings', Debug::settingsAsArray());
        $html .= $this->formatArrayAsHtml('globals', Debug::globalsAsArray());
        $html .= $this->formatArrayAsHtml('php', Debug::phpInfoAsArray());
        $html .= $this->formatArrayAsHtml('symfony', Debug::symfonyInfoAsArray());
        return $html;
    }
    /**
     * Converts an array to HTML.
     *
     * @param string $id The identifier to use
     * @param array $values The array of values
     *
     * @return string An HTML string
     */
    protected function formatArrayAsHtml($id, $values)
    {
        $id = ucfirst(strtolower($id));
        return '
    <h2>' . $id . ' ' . $this->getToggler('sfWebDebug' . $id) . '</h2>
    <div id="sfWebDebug' . $id . '" style="display: none"><pre>' . htmlspecialchars(Yaml::dump(Debug::removeObjects($values)), ENT_QUOTES, Config::get('sf_charset')) . '</pre></div>
    ';
    }
}
class_alias(WebDebugPanelConfig::class, 'sfWebDebugPanelConfig', false);