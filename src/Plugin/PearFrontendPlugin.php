<?php

namespace Symfony1\Components\Plugin;

use PEAR_Frontend_CLI;
use Symfony1\Components\Event\EventDispatcher;
use Symfony1\Components\Event\Event;
use function explode;
use function wordwrap;
use function trim;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/*require_once 'PEAR/Frontend.php';

require_once 'PEAR/Frontend/CLI.php';*/
/**
 * The PEAR Frontend object.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PearFrontendPlugin extends PEAR_Frontend_CLI
{
    protected $dispatcher;
    /**
     * Sets the sfEventDispatcher object for this frontend.
     *
     * @param EventDispatcher $dispatcher The sfEventDispatcher instance
     */
    public function setEventDispatcher(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    public function _displayLine($text)
    {
        $this->_display($text);
    }
    public function _display($text)
    {
        $this->dispatcher->notify(new Event($this, 'application.log', $this->splitLongLine($text)));
    }
    protected function splitLongLine($text)
    {
        $lines = array();
        foreach (explode("\n", $text) as $longline) {
            foreach (explode("\n", wordwrap($longline, 62)) as $line) {
                if ($line = trim($line)) {
                    $lines[] = $line;
                }
            }
        }
        return $lines;
    }
}
class_alias(PearFrontendPlugin::class, 'sfPearFrontendPlugin', false);