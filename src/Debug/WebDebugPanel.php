<?php

namespace Symfony1\Components\Debug;

use Symfony1\Components\Log\Logger;
use Symfony1\Components\Config\Config;
use Symfony1\Components\Util\Toolkit;
use ReflectionClass;
use function get_class;
use function array_reverse;
use function array_keys;
use function strpos;
use function preg_match;
use function sprintf;
use function ini_get;
use function htmlspecialchars;
use function strtr;
use function preg_replace;
use const ENT_QUOTES;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfWebDebugPanel represents a web debug panel.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
abstract class WebDebugPanel
{
    protected $webDebug;
    protected $status = Logger::INFO;
    /**
     * Constructor.
     *
     * @param WebDebug $webDebug The web debug toolbar instance
     */
    public function __construct(WebDebug $webDebug)
    {
        $this->webDebug = $webDebug;
    }
    /**
     * Gets the link URL for the link.
     *
     * @return string The URL link
     */
    public function getTitleUrl()
    {
    }
    /**
     * Gets the text for the link.
     *
     * @return string The link text
     */
    public abstract function getTitle();
    /**
     * Gets the title of the panel.
     *
     * @return string The panel title
     */
    public abstract function getPanelTitle();
    /**
     * Gets the panel HTML content.
     *
     * @return string The panel HTML content
     */
    public abstract function getPanelContent();
    /**
     * Returns the current status.
     *
     * @return int A {@link sfLogger} priority constant
     */
    public function getStatus()
    {
        return $this->status;
    }
    /**
     * Sets the current panel's status.
     *
     * @param int $status A {@link sfLogger} priority constant
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
    /**
     * Returns a toggler element.
     *
     * @param string $element The value of an element's DOM id attribute
     * @param string $title A title attribute
     *
     * @return string
     */
    public function getToggler($element, $title = 'Toggle details')
    {
        return '<a href="#" onclick="sfWebDebugToggle(\'' . $element . '\'); return false;" title="' . $title . '"><img src="' . $this->webDebug->getOption('image_root_path') . '/toggle.gif" alt="' . $title . '"/></a>';
    }
    /**
     * Returns a toggleable presentation of a debug stack.
     *
     * @param array $debugStack
     *
     * @return string
     */
    public function getToggleableDebugStack($debugStack)
    {
        static $i = 1;
        if (!$debugStack) {
            return '';
        }
        $element = get_class($this) . 'Debug' . $i++;
        $keys = array_reverse(array_keys($debugStack));
        $html = $this->getToggler($element, 'Toggle debug stack');
        $html .= '<div class="sfWebDebugDebugInfo" id="' . $element . '" style="display:none">';
        foreach ($debugStack as $j => $trace) {
            $file = isset($trace['file']) ? $trace['file'] : null;
            $line = isset($trace['line']) ? $trace['line'] : null;
            $isProjectFile = $file && 0 === strpos($file, Config::get('sf_root_dir')) && !preg_match('/(cache|plugins|vendor)/', $file);
            $html .= sprintf('<span%s>#%s &raquo; ', $isProjectFile ? ' class="sfWebDebugHighlight"' : '', $keys[$j] + 1);
            if (isset($trace['function'])) {
                $html .= sprintf('in <span class="sfWebDebugLogInfo">%s%s%s()</span> ', isset($trace['class']) ? $trace['class'] : '', isset($trace['type']) ? $trace['type'] : '', $trace['function']);
            }
            $html .= sprintf('from %s line %s', $this->formatFileLink($file, $line), $line);
            $html .= '</span><br/>';
        }
        $html .= "</div>\n";
        return $html;
    }
    /**
     * Formats a file link.
     *
     * @param string $file A file path or class name
     * @param int $line
     * @param string $text Text to use for the link
     *
     * @return string
     */
    public function formatFileLink($file, $line = null, $text = null)
    {
        // this method is called a lot so we avoid calling class_exists()
        if ($file && !Toolkit::isPathAbsolute($file)) {
            if (null === $text) {
                $text = $file;
            }
            // translate class to file name
            $r = new ReflectionClass($file);
            $file = $r->getFileName();
        }
        $shortFile = Debug::shortenFilePath($file);
        if ($linkFormat = Config::get('sf_file_link_format', ini_get('xdebug.file_link_format'))) {
            // return a link
            return sprintf('<a href="%s" class="sfWebDebugFileLink" title="%s">%s</a>', htmlspecialchars(strtr($linkFormat, array('%f' => $file, '%l' => $line)), ENT_QUOTES, Config::get('sf_charset')), htmlspecialchars($shortFile, ENT_QUOTES, Config::get('sf_charset')), null === $text ? $shortFile : $text);
        }
        if (null === $text) {
            // return the shortened file path
            return $shortFile;
        }
        // return the provided text with the shortened file path as a tooltip
        return sprintf('<span title="%s">%s</span>', $shortFile, $text);
    }
    /**
     * Format a SQL string with some colors on SQL keywords to make it more readable.
     *
     * @param string $sql SQL string to format
     *
     * @return string $newSql The new formatted SQL string
     */
    public function formatSql($sql)
    {
        return preg_replace('/\\b(UPDATE|SET|SELECT|FROM|AS|LIMIT|ASC|COUNT|DESC|WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|DISTINCT|DELETE|INSERT|INTO|VALUES)\\b/', '<span class="sfWebDebugLogInfo">\\1</span>', $sql);
    }
}
class_alias(WebDebugPanel::class, 'sfWebDebugPanel', false);