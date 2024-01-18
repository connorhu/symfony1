<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class Symfony1ApplicationTestCase extends TestCase
{
    public function resetSfConfig()
    {
        sfConfig::set('sf_symfony_lib_dir', realpath(__DIR__.'/../lib'));
        sfConfig::set('sf_test_cache_dir', sys_get_temp_dir().'/sf_test_project');
    }

    public function getEnvironment()
    {
        return 'test';
    }

    public function getDebug()
    {
        return true;
    }

    public function getEventDispatcher()
    {
        return null;
    }

    public function getRootDir()
    {
        return null;
    }

    public function projectSetup(sfApplicationConfiguration $applicationConfiguration) {}

    public function getApplicationConfiguration()
    {
        return new TestCaseDrivenApplicationConfiguration(
            $this,
            $this->getEnvironment(),
            $this->getDebug(),
            $this->getRootDir(),
            $this->getEventDispatcher()
        );
    }
}
