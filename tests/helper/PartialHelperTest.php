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
require_once __DIR__.'/../fixtures/MyTestPartialView.php';
require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../../lib/helper/PartialHelper.php';

// Fixme: make this test more beautiful and extend it
/**
 * @internal
 *
 * @coversNothing
 */
class PartialHelperTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $this->diag('get_partial()');
        sfConfig::set('mod_module_partial_view_class', 'MyTest');

        $this->is(get_partial('module/dummy'), '==RENDERED==', 'get_partial() uses the class specified in partial_view_class for the given module');
        $this->is(get_partial('MODULE/dummy'), '==RENDERED==', 'get_partial() accepts a case-insensitive module name');

        // slots tests
        sfContext::getInstance()->inject('response', 'sfWebResponse');

        $this->diag('get_slot()');
        $this->is(get_slot('foo', 'baz'), 'baz', 'get_slot() retrieves default slot content');
        slot('foo', 'bar');
        $this->is(get_slot('foo', 'baz'), 'bar', 'get_slot() retrieves slot content');

        $this->diag('has_slot()');
        $this->ok(has_slot('foo'), 'has_slot() checks if a slot exists');
        $this->ok(!has_slot('doo'), 'has_slot() checks if a slot does not exist');

        $this->diag('include_slot()');
        ob_start();
        include_slot('foo');
        $this->is(ob_get_clean(), 'bar', 'include_slot() prints out the content of an existing slot');

        ob_start();
        include_slot('doo');
        $this->is(ob_get_clean(), '', 'include_slot() does not print out the content of an unexisting slot');

        ob_start();
        include_slot('doo', 'zoo');
        $this->is(ob_get_clean(), 'zoo', 'include_slot() prints out the default content specified for an unexisting slot');
    }
}
