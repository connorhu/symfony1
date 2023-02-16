<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../PhpUnitSfTestHelperTrait.php';
require_once __DIR__.'/../sfParameterHolderProxyTestCase.php';
require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../fixtures/myView.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfViewParameterHolderTest extends sfParameterHolderProxyTestCase
{
    protected function setUp(): void
    {
        $this->methodName = 'parameter';

        $context = sfContext::getInstance(array('request' => 'sfWebRequest', 'response' => 'sfWebResponse'), true);
        $this->object = new myView($context, '', '', '');
    }
}
