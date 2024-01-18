<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TestCaseDrivenApplicationConfiguration extends sfApplicationConfiguration implements TestCaseDrivenConfigurationInterface
{
    protected $testCase;

    public function __construct($testCase, $environment, $debug, $rootDir = null, sfEventDispatcher $dispatcher = null)
    {
        $this->testCase = $testCase;
        parent::__construct($environment, $debug, $rootDir, $dispatcher);
    }

    public function getTestCase()
    {
        return $this->testCase;
    }

    public function getI18NGlobalDirs()
    {
        if (method_exists($this->testCase, 'getI18NGlobalDirs')) {
            return $this->testCase->getI18NGlobalDirs();
        }

        return parent::getI18NGlobalDirs();
    }
}
