<?php

namespace Symfony1\Components\Plugin;

use Symfony1\Components\Exception\Exception;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfPluginException.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PluginException extends Exception
{
}
class_alias(PluginException::class, 'sfPluginException', false);