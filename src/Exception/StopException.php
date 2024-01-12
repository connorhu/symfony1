<?php

namespace Symfony1\Components\Exception;

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfStopException is thrown when you want to stop action flow.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
class StopException extends Exception
{
    /**
     * Stops the current action.
     */
    public function printStackTrace()
    {
    }
}
class_alias(StopException::class, 'sfStopException', false);