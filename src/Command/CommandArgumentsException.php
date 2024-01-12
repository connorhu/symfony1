<?php

namespace Symfony1\Components\Command;

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfCommandException is thrown when an error occurs in a task.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class CommandArgumentsException extends CommandException
{
}
class_alias(CommandArgumentsException::class, 'sfCommandArgumentsException', false);