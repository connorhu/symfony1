<?php

namespace Symfony1\Components\Plugin;

use PEAR_REST;
use function array_merge;
use const SYMFONY_VERSION;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
// require_once 'PEAR/REST.php';
/**
 * sfPearRest interacts with a PEAR channel.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PearRest extends PEAR_REST
{
    /**
     * @see PEAR_REST::downloadHttp()
     *
     * @param (mixed | null) $lastmodified
     */
    public function downloadHttp($url, $lastmodified = null, $accept = false, $channel = false)
    {
        return parent::downloadHttp($url, $lastmodified, array_merge(false !== $accept ? $accept : array(), array("\r\nX-SYMFONY-VERSION: " . SYMFONY_VERSION)));
    }
}
class_alias(PearRest::class, 'sfPearRest', false);