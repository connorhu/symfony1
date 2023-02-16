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
require_once realpath(__DIR__.'/../fixtures/symfony/config/ProjectConfiguration.class.php');

/**
 * @internal
 *
 * @coversNothing
 */
class sfContextTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $this->markTestSkipped();
        return;
        // use functional project configruration

        $frontend_context = sfContext::createInstance(ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true));
        $frontend_context_prod = sfContext::createInstance(ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false));
        $i18n_context = sfContext::createInstance(ProjectConfiguration::getApplicationConfiguration('i18n', 'test', true));
        $cache_context = sfContext::createInstance(ProjectConfiguration::getApplicationConfiguration('cache', 'test', true));

        // ::getInstance()
        $this->diag('::getInstance()');
        $this->assertInstanceOf(sfContext::class, $frontend_context, '::createInstance() takes an application configuration and returns application context instance');
        $this->isa_ok(sfContext::getInstance('frontend'), 'sfContext', '::createInstance() creates application name context instance');

        $context = sfContext::getInstance('frontend');
        $context1 = sfContext::getInstance('i18n');
        $context2 = sfContext::getInstance('cache');
        $this->is(sfContext::getInstance('i18n'), $context1, '::getInstance() returns the named context if it already exists');

        // ::switchTo();
        $this->diag('::switchTo()');
        sfContext::switchTo('i18n');
        $this->is(sfContext::getInstance(), $context1, '::switchTo() changes the default context instance returned by ::getInstance()');
        sfContext::switchTo('cache');
        $this->is(sfContext::getInstance(), $context2, '::switchTo() changes the default context instance returned by ::getInstance()');

        // ->get() ->set() ->has()
        $this->diag('->get() ->set() ->has()');
        $this->is($context1->has('object'), false, '->has() returns false if no object of the given name exist');
        $object = new stdClass();
        $context1->set('object', $object, '->set() stores an object in the current context instance');
        $this->is($context1->has('object'), true, '->has() returns true if an object is stored for the given name');
        $this->is($context1->get('object'), $object, '->get() returns the object associated with the given name');
        try {
            $context1->get('object1');
            $this->fail('->get() throws an sfException if no object is stored for the given name');
        } catch (sfException $e) {
            $this->pass('->get() throws an sfException if no object is stored for the given name');
        }

        $context['foo'] = $frontend_context;
        $this->diag('Array access for context objects');
        $this->is(isset($context['foo']), true, '->offsetExists() returns true if context object exists');
        $this->is(isset($context['foo2']), false, '->offsetExists() returns false if context object does not exist');
        $this->isa_ok($context['foo'], 'sfContext', '->offsetGet() returns attribute by name');

        $context['foo2'] = $i18n_context;
        $this->isa_ok($context['foo2'], 'sfContext', '->offsetSet() sets object by name');

        unset($context['foo2']);
        $this->is(isset($context['foo2']), false, '->offsetUnset() unsets object by name');

        $this->diag('->__call()');

        $context->setFoo4($i18n_context);
        $this->is($context->has('foo4'), true, '->__call() sets context objects by name using setName()');
        $this->isa_ok($context->getFoo4(), 'sfContext', '->__call() returns context objects by name using getName()');

        try {
            $context->unknown();
            $this->fail('->__call() throws an sfException if factory / method does not exist');
        } catch (sfException $e) {
            $this->pass('->__call() throws an sfException if factory / method does not exist');
        }

        $this->diag('->getServiceContainer() test');
        $sc = $frontend_context->getServiceContainer();

        $this->ok(file_exists(sfConfig::get('sf_cache_dir').'/frontend/test/config/config_services.yml.php'), '->getServiceContainer() creates a cache file in /cache/frontend/test/config');
        $this->ok(class_exists('frontend_testServiceContainer'), '->getServiceContainer() creates and loads the frontend_testServiceContainer class');
        $this->ok($sc instanceof frontend_testServiceContainer, '->getServiceContainer() returns an instance of frontend_testServiceContainer');
        $this->ok($sc->hasService('my_app_service'), '->getServiceContainer() contains app/config/service.yml services');
        $this->ok($sc->hasService('my_project_service'), '->getServiceContainer() contains /config/service.yml services');
        $this->ok($sc->hasService('my_plugin_service'), '->getServiceContainer() contains plugin/config/service.yml services');
        $this->is($sc->getParameter('sf_root_dir'), realpath(__DIR__.'/../fixtures/symfony'), '->getServiceContainer() sfConfig parameters are accessibles');
        $this->ok($sc->hasParameter('my_app_test_param'), '->getServiceContainer() contains env specifiv parameters');

        $this->diag('->getServiceContainer() prod');
        $sc = $frontend_context_prod->getServiceContainer();
        $this->ok(file_exists(sfConfig::get('sf_cache_dir').'/frontend/prod/config/config_services.yml.php'), '->getServiceContainer() creates a cache file in /cache/frontend/prod/config');
        $this->ok(class_exists('frontend_prodServiceContainer'), '->getServiceContainer() creates and loads the frontend_prodServiceContainer class');
        $this->ok($sc instanceof frontend_prodServiceContainer, '->getServiceContainer() returns an instance of frontend_prodServiceContainer');
        $this->ok(false === $sc->hasParameter('my_app_test_param'), '->getServiceContainer() does not contain other env specifiv parameters');
    }
}
