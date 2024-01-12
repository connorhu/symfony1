<?php

namespace Symfony1\Components\Plugin;

use PEAR_REST_10;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
// require_once 'PEAR/REST/10.php';
/**
 * sfPearRest10 interacts with a PEAR channel that supports REST 1.0.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PearRest10 extends PEAR_REST_10
{
    /**
     * @see PEAR_REST_10
     */
    public function __construct($config, $options = array())
    {
        $class = isset($options['base_class']) ? $options['base_class'] : 'sfPearRest';
        $this->_rest = new $class($config, $options);
    }
}
class_alias(PearRest10::class, 'sfPearRest10', false);