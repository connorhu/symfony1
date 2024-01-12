<?php

namespace Symfony1\Components\Plugin;

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfPluginDependencyException.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class PluginDependencyException extends PluginException
{
}
class_alias(PluginDependencyException::class, 'sfPluginDependencyException', false);