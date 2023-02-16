<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../lib/test/Symfony1ProjectTestCase.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfProjectConfigurationNonExistentPluginTest extends Symfony1ProjectTestCase
{
    public function projectSetup(sfProjectConfiguration $configuration)
    {
        $configuration->enablePlugins('NonExistantPlugin');
    }

    public function testEnableNonExistantPlugin()
    {
        $this->expectException(\InvalidArgumentException::class);

        $configuration = $this->getProjectConfiguration();
    }
}
