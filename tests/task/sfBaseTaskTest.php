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
require_once __DIR__.'/../fixtures/TestTask.php';
require_once __DIR__.'/../fixtures/ApplicationTask.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfBaseTaskTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $this->markTestSkipped('make this test work');
        return; // TODO fix internal state of sfCacheClearTask and this

        $rootDir = __DIR__.'/../../test/functional/fixtures';

        sfToolkit::clearDirectory(__DIR__.'/../../test/functional/fixtures/cache');
        
        $dispatcher = new sfEventDispatcher();
        $configuration = new ProjectConfiguration(__DIR__.'/../../test/functional/fixtures', $dispatcher);
        $autoload = sfSimpleAutoload::getInstance();

        $task = new TestTask($dispatcher, new sfFormatter());
        
        // ->initializeAutoload()
        $this->diag('->initializeAutoload()');
        
        $this->is($autoload->getClassPath('myLibClass'), null, 'no project classes are autoloaded before ->initializeAutoload()');
        
        $task->initializeAutoload($configuration);
        
        $this->ok(null !== $autoload->getClassPath('myLibClass'), '->initializeAutoload() loads project classes');
        $this->ok(null !== $autoload->getClassPath('BaseExtendMe'), '->initializeAutoload() includes plugin classes');
        $this->is($autoload->getClassPath('ExtendMe'), sfConfig::get('sf_lib_dir').'/ExtendMe.class.php', '->initializeAutoload() prefers project to plugin classes');
        
        $task->initializeAutoload($configuration, true);
        $this->is($autoload->getClassPath('ExtendMe'), sfConfig::get('sf_lib_dir').'/ExtendMe.class.php', '->initializeAutoload() prefers project to plugin classes after reload');
        
        // ->run()
        $this->diag('->run()');
        
        chdir($rootDir);
        
        $task = new ApplicationTask($dispatcher, new sfFormatter());
        try {
            $task->run();
            $this->pass('->run() creates an application configuration if none is set');
        } catch (Exception $e) {
            $this->diag($e->getMessage());
            $this->fail('->run() creates an application configuration if none is set');
        }
        
        $task = new ApplicationTask($dispatcher, new sfFormatter());
        $task->setConfiguration($configuration);
        try {
            $task->run();
            $this->pass('->run() creates an application configuration if only a project configuration is set');
        } catch (Exception $e) {
            $this->diag($e->getMessage());
            $this->fail('->run() creates an application configuration if only a project configuration is set');
        }
        
        // ->getServiceContainer()
        $this->diag('->getServiceContainer()');
        $serviceContainer = $task->getServiceContainer();
        
        $this->ok($serviceContainer instanceof sfServiceContainer, '->getServiceContainer() returns an sfServiceContainer');
        $this->is($serviceContainer, $task->getServiceContainer(), '->getServiceContainer() returns always the same instance');
        $this->ok($serviceContainer->hasService('my_project_service'), '->getServiceContainer() is correctly configured');
        
        // ->getRouting()
        $this->diag('->getRouting()');
        $routing = $task->getRouting();
        
        $this->ok($routing instanceof sfRouting, '->getRouting() returns an sfPatternRouting');
        $this->is($routing, $task->getRouting(), '->getRouting() returns always the same instance');
        $this->ok($routing->hasRouteName('homepage'), '->getRouting() is correctly configured');
        
        // ->getMailer()
        $this->diag('->getMailer()');
        $mailer = $task->getMailer();
        
        $this->ok($mailer instanceof sfMailer, '->getMailer() returns an sfMailer');
        $this->is($mailer, $task->getMailer(), '->getMailer() returns always the same instance');
        $this->is($mailer->getDeliveryStrategy(), sfMailer::REALTIME, '->getMailer() is correctly configured');

    }
}
