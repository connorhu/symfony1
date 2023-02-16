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
// require_once __DIR__.'/../fixtures/FILENAME.php';
require_once __DIR__.'/sfTestPearDownloader.class.php';
require_once __DIR__.'/sfTestPearRest.class.php';
require_once __DIR__.'/sfPluginTestHelper.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfPearEnvironmentTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // setup
        $temp = @tempnam('/tmp/sf_plugin_test', 'tmp');
        unlink($temp);
        mkdir($temp, 0777, true);

        define('SF_PLUGIN_TEST_DIR', $temp);

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

        foreach (array('plugin_dir', 'cache_dir') as $option) {
            try {
                $localOptions = $options;
                unset($localOptions[$option]);
                $environment = new sfPearEnvironment($dispatcher, $localOptions);

                $this->fail(sprintf('->initialize() throws an exception if you don\'t pass a "%s" option', $option));
            } catch (sfException $e) {
                $this->pass(sprintf('->initialize() throws an exception if you don\'t pass a "%s" option', $option));
            }
        }

        // ->registerChannel()
        $this->diag('->registerChannel()');
        $environment = new sfPearEnvironment($dispatcher, $options);
        $environment->registerChannel('pear.example.com', true);
        $this->pass('->registerChannel() registers a PEAR channel');

        // teardown
        sfToolkit::clearDirectory($temp);
        rmdir($temp);
    }
}
