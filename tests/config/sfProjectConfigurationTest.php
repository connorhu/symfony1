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
class sfProjectConfigurationTest extends Symfony1ProjectTestCase
{
    public function projectSetup(sfProjectConfiguration $configuration)
    {
        $configuration->enablePlugins(array('sfAutoloadPlugin', 'sfConfigPlugin'));
        $configuration->setPluginPath('sfConfigPlugin', $configuration->getRootDir().'/lib/plugins/sfConfigPlugin');
    }

    /**
     * @dataProvider lateMethodCallExceptionDataProvider
     */
    public function testLateMethodCallException(string $methodName)
    {
        $this->expectException(\LogicException::class);

        $configuration = $this->getProjectConfiguration();

        $configuration->{$methodName}(array());
    }

    public static function lateMethodCallExceptionDataProvider(): Generator
    {
        yield array('setPlugins');
        yield array('disablePlugins');
        yield array('enablePlugins');
        yield array('enableAllPluginsExcept');
    }

    public function testEnabledPlugins()
    {
        $configuration = $this->getProjectConfiguration();

        $this->assertSame(array('sfAutoloadPlugin', 'sfConfigPlugin'), $configuration->getPlugins());
    }
}
