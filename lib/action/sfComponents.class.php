<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony1\Components\Action\Components;

$errorMessage = sprintf('Using the "%s" class is deprecated since symfony1 version 1.6, use "%s" instead.', sfComponents::class, Components::class);
@trigger_error($errorMessage, \E_USER_DEPRECATED);

/** @deprecated since symfony1 1.6, use "Symfony1\Components\Action\Components" instead */
abstract class sfComponents extends Components {}
