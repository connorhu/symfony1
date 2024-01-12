<?php

namespace Symfony1\Components\Test;

use ProjectConfiguration;
use Symfony1\Components\Event\EventDispatcher;
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TestCaseDrivenProjectConfiguration extends ProjectConfiguration implements TestCaseDrivenConfigurationInterface
{
    protected $testCase;
    public function __construct($testCase, $rootDir = null, EventDispatcher $dispatcher = null)
    {
        $this->testCase = $testCase;
        parent::__construct($rootDir, $dispatcher);
    }
    public function getTestCase()
    {
        return $this->testCase;
    }
}
class_alias(TestCaseDrivenProjectConfiguration::class, 'TestCaseDrivenProjectConfiguration', false);