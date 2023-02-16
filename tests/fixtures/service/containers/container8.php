<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$container = new sfServiceContainerBuilder();
$container->setParameters(array(
    'FOO' => 'bar',
    'bar' => 'foo is %foo bar',
    'values' => array(true, false, null, 0, 1000.3, 'true', 'false', 'null'),
));

return $container;
