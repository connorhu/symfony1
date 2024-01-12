<?php

namespace Symfony1\Components\Action;

use Symfony1\Components\Request\Request;
use Symfony1\Components\Exception\InitializationException;
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfComponents.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
abstract class Components extends Component
{
    /**
     * @param Request $request
     *
     * @throws InitializationException
     *
     * @see sfComponent
     */
    public function execute($request)
    {
        throw new InitializationException('sfComponents initialization failed.');
    }
}
class_alias(Components::class, 'sfComponents', false);