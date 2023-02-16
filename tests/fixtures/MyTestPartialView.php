<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MyTestPartialView extends sfPartialView
{
    public function render()
    {
        return '==RENDERED==';
    }

    public function initialize($context, $moduleName, $actionName, $viewName) {}

    public function setPartialVars(array $partialVars) {}
}
