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
class Symfony1ProjectTestCase extends TestCase
{
    public function resetSfConfig()
    {
        sfConfig::clear();
        sfConfig::set('sf_symfony_lib_dir', realpath(__DIR__.'/../'));
        sfConfig::set('sf_test_cache_dir', $tempDir = sys_get_temp_dir().'/sf_test_project');

        // TODO make this uniq
        if (!is_dir($tempDir)) {
            mkdir($tempDir);
        }
    }

    public function projectSetup(sfProjectConfiguration $configuration) {}

    public function getEventDispatcher()
    {
        return null;
    }

    public function getRootDir()
    {
        return null;
    }

    public function getProjectConfiguration()
    {
        return new TestCaseDrivenProjectConfiguration($this, $this->getRootDir(), $this->getEventDispatcher());
    }
}
