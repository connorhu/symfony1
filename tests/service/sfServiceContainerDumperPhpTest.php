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

/**
 * @internal
 *
 * @coversNothing
 */
class sfServiceContainerDumperPhpTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dir = __DIR__.'/../fixtures/service/php';

        // ->dump()
        $this->diag('->dump()');
        $dumper = new sfServiceContainerDumperPhp($container = new sfServiceContainerBuilder());

        $this->is($dumper->dump(), file_get_contents($dir.'/services1.php'), '->dump() dumps an empty container as an empty PHP class');
        $this->is($dumper->dump(array('class' => 'Container', 'base_class' => 'AbstractContainer')), file_get_contents($dir.'/services1-1.php'), '->dump() takes a class and a base_class options');

        $container = new sfServiceContainerBuilder();
        $dumper = new sfServiceContainerDumperPhp($container);

        // ->addParameters()
        $this->diag('->addParameters()');
        $container = include __DIR__.'/../fixtures/service/containers/container8.php';
        $dumper = new sfServiceContainerDumperPhp($container);
        $this->is($dumper->dump(), file_get_contents($dir.'/services8.php'), '->dump() dumps parameters');

        // ->addService()
        $this->diag('->addService()');
        $container = include __DIR__.'/../fixtures/service/containers/container9.php';
        $dumper = new sfServiceContainerDumperPhp($container);
        $this->is($dumper->dump(), str_replace('%path%', realpath(__DIR__.'/../fixtures/service/includes'), file_get_contents($dir.'/services9.php')), '->dump() dumps services');

        $dumper = new sfServiceContainerDumperPhp($container = new sfServiceContainerBuilder());
        $container->register('foo', 'FooClass')->addArgument(new stdClass());
        try {
            $dumper->dump();
            $this->fail('->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
        } catch (RuntimeException $e) {
            $this->pass('->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
        }
    }
}
