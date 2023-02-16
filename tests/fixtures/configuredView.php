<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class configuredView extends myView
{
    public static $isDecorated = false;

    public function initialize($context, $moduleName, $actionName, $viewName)
    {
        $this->setDecorator(self::$isDecorated);

        parent::initialize($context, $moduleName, $actionName, $viewName);
    }
}
