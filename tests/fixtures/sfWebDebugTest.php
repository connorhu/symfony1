<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @internal
 *
 * @coversNothing
 */
class sfWebDebugTest extends sfWebDebug
{
    public function __construct()
    {
        $this->options['image_root_path'] = '';
        $this->options['request_parameters'] = array();
    }
}
