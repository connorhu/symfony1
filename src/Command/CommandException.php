<?php

namespace Symfony1\Components\Command;

use Symfony1\Components\Exception\Exception;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
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
class CommandException extends Exception
{
}
class_alias(CommandException::class, 'sfCommandException', false);