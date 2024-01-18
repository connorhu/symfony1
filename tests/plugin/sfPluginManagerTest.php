<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../PhpUnitSfTestHelperTrait.php';
require_once __DIR__.'/../fixtures/myPluginManager.php';
require_once __DIR__.'/sfTestPearDownloader.class.php';
require_once __DIR__.'/sfTestPearRest.class.php';
require_once __DIR__.'/sfPluginTestHelper.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfPluginManagerTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // setup
        $temp = @tempnam('/tmp/sf_plugin_test', 'tmp');
        unlink($temp);
        mkdir($temp, 0777, true);

        @define('SF_PLUGIN_TEST_DIR', $temp);

        $options = array(
            'plugin_dir' => $temp.'/plugins',
            'cache_dir' => $temp.'/cache',
            'preferred_state' => 'stable',
            'rest_base_class' => 'sfTestPearRest',
            'downloader_base_class' => 'sfTestPearDownloader',
        );

        $dispatcher = new sfEventDispatcher();

        // ->initialize()
        $this->diag('->initialize()');
        $environment = new sfPearEnvironment($dispatcher, $options);
        $pluginManager = new myPluginManager($dispatcher, $environment);
        $this->is($pluginManager->getEnvironment(), $environment, '->initialize() takes a sfPearEnvironment as its second argument');

        // ->installPlugin() ->uninstallPlugin()
        $this->diag('->installPlugin() ->uninstallPlugin');
        $pluginManager->installPlugin('sfTestPlugin');
        $this->is(file_get_contents($temp.'/plugins/sfTestPlugin/VERSION'), '1.0.3', '->installPlugin() installs the latest stable version');

        $this->ok($pluginManager->uninstallPlugin('sfTestPlugin'), '->uninstallPlugin() returns true if the plugin is properly uninstalled');
        $this->ok(!is_file($temp.'/plugins/sfTestPlugin/VERSION'), '->uninstallPlugin() uninstalls a plugin');

        $pluginManager->installPlugin('sfTestPlugin', array('stability' => 'beta'));
        $this->is(file_get_contents($temp.'/plugins/sfTestPlugin/VERSION'), '1.0.4', '->installPlugin() can take a stability option');

        $this->ok($pluginManager->uninstallPlugin('sfTestPlugin'), '->uninstallPlugin() returns true if the plugin is properly uninstalled');
        $this->ok(!is_file($temp.'/plugins/sfTestPlugin/VERSION'), '->uninstallPlugin() uninstalls a plugin');

        $pluginManager->installPlugin('sfTestPlugin', array('version' => '1.0.0'));
        $this->is(file_get_contents($temp.'/plugins/sfTestPlugin/VERSION'), '1.0.0', '->installPlugin() can take a version option');

        $this->ok($pluginManager->uninstallPlugin('sfTestPlugin'), '->uninstallPlugin() returns true if the plugin is properly uninstalled');
        $this->ok(!is_file($temp.'/plugins/sfTestPlugin/VERSION'), '->uninstallPlugin() uninstalls a plugin');

        $this->diag('Try to install a version that won\'t work with our main package');

        try {
            $pluginManager->installPlugin('sfTestPlugin', array('version' => '1.1.3'));

            $this->fail('->installPlugin() throws an exception if you try to install a version that is not compatible with our main package');
        } catch (sfPluginDependencyException $e) {
            $this->pass('->installPlugin() throws an exception if you try to install a version that is not compatible with our main package');
        }

        $this->diag('Upgrade our main package to 1.1.0');
        $pluginManager->setMainPackageVersion('1.1.0');

        $pluginManager->installPlugin('sfTestPlugin');
        $this->is(file_get_contents($temp.'/plugins/sfTestPlugin/VERSION'), '1.1.3', '->installPlugin() installs the latest stable version');

        $this->ok($pluginManager->uninstallPlugin('sfTestPlugin'), '->uninstallPlugin() returns true if the plugin is properly uninstalled');
        $this->ok(!is_file($temp.'/plugins/sfTestPlugin/VERSION'), '->uninstallPlugin() uninstalls a plugin');

        $pluginManager->installPlugin('sfTestPlugin', array('stability' => 'beta'));
        $this->is(file_get_contents($temp.'/plugins/sfTestPlugin/VERSION'), '1.1.4', '->installPlugin() takes a stability as its 4th argument');

        $this->ok($pluginManager->uninstallPlugin('sfTestPlugin'), '->uninstallPlugin() returns true if the plugin is properly uninstalled');
        $this->ok(!is_file($temp.'/plugins/sfTestPlugin/VERSION'), '->uninstallPlugin() uninstalls a plugin');

        $this->diag('try to uninstall a non installed plugin');
        $this->ok(!$pluginManager->uninstallPlugin('sfFooPlugin'), '->uninstallPlugin() returns false if the plugin is not installed');

        $this->diag('try to install a non existant plugin');
        try {
            $pluginManager->installPlugin('sfBarPlugin');

            $this->fail('->installPlugin() throws an exception if the plugin does not exist');
        } catch (sfPluginException $e) {
            $this->pass('->installPlugin() throws an exception if the plugin does not exist');
        }

        $pluginManager->installPlugin('http://pear.example.com/get/sfTestPlugin/sfTestPlugin-1.1.4.tgz');
        $this->is(file_get_contents($temp.'/plugins/sfTestPlugin/VERSION'), '1.1.4', '->installPlugin() can install a PEAR package hosted on a website');

        $this->ok($pluginManager->uninstallPlugin('sfTestPlugin'), '->uninstallPlugin() returns true if the plugin is properly uninstalled');
        $this->ok(!is_file($temp.'/plugins/sfTestPlugin/VERSION'), '->uninstallPlugin() uninstalls a plugin');

        $pluginManager->installPlugin(__DIR__.'/../fixtures/plugin/http/pear.example.com/get/sfTestPlugin/sfTestPlugin-1.1.4.tgz');
        $this->is(file_get_contents($temp.'/plugins/sfTestPlugin/VERSION'), '1.1.4', '->installPlugin() can install a local PEAR package');

        $this->ok($pluginManager->uninstallPlugin('sfTestPlugin'), '->uninstallPlugin() returns true if the plugin is properly uninstalled');
        $this->ok(!is_file($temp.'/plugins/sfTestPlugin/VERSION'), '->uninstallPlugin() uninstalls a plugin');

        // ->getPluginVersion()
        $this->diag('->getPluginVersion()');
        $pluginManager->setMainPackageVersion('1.0.0');
        $this->is($pluginManager->getPluginVersion('sfTestPlugin'), '1.0.3', '->getPluginVersion() returns the latest version available for the plugin');
        $this->is($pluginManager->getPluginVersion('sfTestPlugin', 'beta'), '1.0.4', '->getPluginVersion() takes a stability as its second argument');
        $pluginManager->setMainPackageVersion('1.1.0');
        $this->is($pluginManager->getPluginVersion('sfTestPlugin'), '1.1.3', '->getPluginVersion() returns the latest version available for the plugin');
        $this->is($pluginManager->getPluginVersion('sfTestPlugin', 'beta'), '1.1.4', '->getPluginVersion() takes a stability as its second argument');
        $this->is($pluginManager->getPluginVersion('sfTestPlugin', 'alpha'), '1.1.4', '->getPluginVersion() takes a stability as its second argument');

        // ->getInstalledPlugins()
        $this->diag('->getInstalledPlugins()');
        $pluginManager->installPlugin('sfTestPlugin');
        $installed = $pluginManager->getInstalledPlugins();
        $a = array($installed[0]->getName(), $installed[1]->getName());
        $b = array('sfTestPlugin', 'sfMainPackage');
        sort($a);
        sort($b);
        $this->is($a, $b, '->getInstalledPlugin() returns an array of installed packages');
        $this->is(count($installed), 2, '->getInstalledPlugin() returns an array of installed packages');
        $pluginManager->uninstallPlugin('sfTestPlugin');

        $this->diag('install a plugin with a dependency must fail');
        try {
            $pluginManager->installPlugin('sfFooPlugin');
            $this->fail('->installPlugin() throws an exception if the plugin needs a dependency to be installed');
        } catch (sfPluginDependencyException $e) {
            $this->pass('->installPlugin() throws an exception if the plugin needs a dependency to be installed');
        }

        $this->diag('install a plugin with a dependency and force installation of all dependencies');
        $pluginManager->installPlugin('sfFooPlugin', array('install_deps' => true));
        $this->is(file_get_contents($temp.'/plugins/sfFooPlugin/VERSION'), '1.0.0', '->installPlugin() can take a install_deps option');
        $this->is(file_get_contents($temp.'/plugins/sfTestPlugin/VERSION'), '1.1.3', '->installPlugin() can take a install_deps option');
        $pluginManager->uninstallPlugin('sfFooPlugin');
        $pluginManager->uninstallPlugin('sfTestPlugin');

        $pluginManager->installPlugin('sfTestPlugin', array('version' => '1.1.4'));
        $pluginManager->installPlugin('sfFooPlugin');
        $this->is(file_get_contents($temp.'/plugins/sfFooPlugin/VERSION'), '1.0.0', '->installPlugin() installs a plugin if all dependencies are installed');
        $this->is(file_get_contents($temp.'/plugins/sfTestPlugin/VERSION'), '1.1.4', '->installPlugin() installs a plugin if all dependencies are installed');
        $pluginManager->uninstallPlugin('sfFooPlugin');
        $pluginManager->uninstallPlugin('sfTestPlugin');

        $this->diag('try to uninstall a plugin with a depedency must fail');
        $pluginManager->installPlugin('sfTestPlugin', array('version' => '1.1.4'));
        $pluginManager->installPlugin('sfFooPlugin');
        try {
            $pluginManager->uninstallPlugin('sfTestPlugin');
            $this->fail('->uninstallPlugin() throws an exception if you try to uninstall a plugin that is needed for another one');
        } catch (sfPluginException $e) {
            $this->pass('->uninstallPlugin() throws an exception if you try to uninstall a plugin that is needed for another one');
        }
        $pluginManager->uninstallPlugin('sfFooPlugin');
        $pluginManager->uninstallPlugin('sfTestPlugin');

        $this->diag('install a plugin with a dependency which is installed by with a too old version');
        $pluginManager->setMainPackageVersion('1.0.0');
        $pluginManager->installPlugin('sfTestPlugin', array('version' => '1.0.4'));
        $pluginManager->setMainPackageVersion('1.1.0');
        try {
            $pluginManager->installPlugin('sfFooPlugin');
            $this->fail('->installPlugin() throws an exception if you try to install a plugin with a dependency that is installed but not in the right version');
        } catch (sfPluginDependencyException $e) {
            $this->pass('->installPlugin() throws an exception if you try to install a plugin with a dependency that is installed but not in the right version');
        }
        $pluginManager->uninstallPlugin('sfTestPlugin');

        $this->diag('install a plugin with a dependency which is installed with a too old version and you want automatic upgrade');
        $pluginManager->setMainPackageVersion('1.0.0');
        $pluginManager->installPlugin('sfTestPlugin', array('version' => '1.0.4'));
        $pluginManager->setMainPackageVersion('1.1.0');
        $pluginManager->installPlugin('sfFooPlugin', array('install_deps' => true));
        $this->is(file_get_contents($temp.'/plugins/sfFooPlugin/VERSION'), '1.0.0', '->installPlugin() installs a plugin if all dependencies are installed');
        $pluginManager->uninstallPlugin('sfFooPlugin');
        $pluginManager->uninstallPlugin('sfTestPlugin');

        // teardown
        sfToolkit::clearDirectory($temp);
        rmdir($temp);
    }
}
