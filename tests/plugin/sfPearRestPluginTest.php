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
require_once __DIR__.'/sfTestPearDownloader.class.php';
require_once __DIR__.'/sfTestPearRest.class.php';
require_once __DIR__.'/sfPluginTestHelper.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfPearRestPluginTest extends TestCase
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
        $environment = new sfPearEnvironment($dispatcher, $options);
        $environment->registerChannel('pear.example.com', true);

        $rest = $environment->getRest();

        // ->getPluginVersions()
        $this->diag('->getPluginVersions()');
        $this->is($rest->getPluginVersions('sfTestPlugin'), array('1.1.3', '1.0.3', '1.0.0'), '->getPluginVersions() returns an array of stable versions for a plugin');
        $this->is($rest->getPluginVersions('sfTestPlugin', 'stable'), array('1.1.3', '1.0.3', '1.0.0'), '->getPluginVersions() accepts stability as a second parameter and returns an array of versions for a plugin based on stability');
        $this->is($rest->getPluginVersions('sfTestPlugin', 'beta'), array('1.0.4', '1.1.4', '1.1.3', '1.0.3', '1.0.0'), '->getPluginVersions() accepts stability as a second parameter and returns an array of versions for a plugin based on stability cascade (beta includes stable)');

        // ->getPluginDependencies()
        $this->diag('->getPluginDependencies()');
        $dependencies = $rest->getPluginDependencies('sfTestPlugin', '1.1.4');
        $this->is($dependencies['required']['package']['min'], '1.1.0', '->getPluginDependencies() returns an array of dependencies');

        // ->getPluginDownloadURL()
        $this->diag('->getPluginDownloadURL()');
        $this->is($rest->getPluginDownloadURL('sfTestPlugin', '1.1.3', 'stable'), 'http://pear.example.com/get/sfTestPlugin/sfTestPlugin-1.1.3.tgz', '->getPluginDownloadURL() returns a plugin URL');

        // teardown
        sfToolkit::clearDirectory($temp);
        rmdir($temp);
    }
}
