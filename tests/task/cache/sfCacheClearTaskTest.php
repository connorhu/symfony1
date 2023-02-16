<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../PhpUnitSfTestHelperTrait.php';
require_once sfConfig::get('sf_symfony_lib_dir').'/command/sfCommandApplication.class.php';
require_once sfConfig::get('sf_symfony_lib_dir').'/command/sfSymfonyCommandApplication.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfCacheClearTaskTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $this->markTestSkipped('make this test work');
        return; // TODO fix internal state of sfBaseTaskTest and this

        // from bootstrap/task.php
        $tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sf_'.rand(11111, 99999);
        mkdir($tmpDir, 0777, true);
        chdir($tmpDir);

        $application = new sfSymfonyCommandApplication(new sfEventDispatcher(), new sfFormatter(), array(
            'symfony_lib_dir' => sfConfig::get('sf_symfony_lib_dir'),
        ));

        $dispatcher = new sfEventDispatcher();
        $formatter = new sfFormatter();
        
        $task = new sfGenerateProjectTask($dispatcher, $formatter);
        $task->run(['test']);

        $task = new sfGenerateAppTask($dispatcher, $formatter);
        $task->run(['frontend']);

        require_once sfConfig::get('sf_root_dir').'/config/ProjectConfiguration.class.php';
        $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true);
        
        // Put something in the cache
        $file = sfConfig::get('sf_config_cache_dir').DIRECTORY_SEPARATOR.'test';
        touch($file);

        $this->ok(file_exists($file), 'The test file is in the cache');

        $task = new sfCacheClearTask($dispatcher, $formatter);
        $task->run();

        $this->ok(!file_exists($file), 'The test file is removed by the cache:clear task');
    }
}
