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
require_once __DIR__.'/../fixtures/FormFormatterStub.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetFormInputFileEditableTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->render()
        $this->diag('->render()');

        try {
            new sfWidgetFormInputFileEditable();
            $this->fail('->render() throws an exception if you don\' pass a "file_src" option.');
        } catch (RuntimeException $e) {
            $this->pass('->render() throws an exception if you don\' pass a "file_src" option.');
        }

        $w = new sfWidgetFormInputFileEditable(array(
            'file_src' => '-foo-',
        ));

        $this->is($w->render('foo'), '-foo-<br /><input type="file" name="foo" id="foo" /><br /><input type="checkbox" name="foo_delete" id="foo_delete" /> <label for="foo_delete">remove the current file</label>', '->render() renders the widget as HTML');

        $this->diag('with_delete option');
        $w = new sfWidgetFormInputFileEditable(array(
            'file_src' => '-foo-',
            'with_delete' => false,
        ));
        $this->is($w->render('foo'), '-foo-<br /><input type="file" name="foo" id="foo" /><br /> ', '->render() renders the widget as HTML');

        $this->diag('delete_label option');
        $w = new sfWidgetFormInputFileEditable(array(
            'file_src' => '-foo-',
            'delete_label' => 'delete',
        ));
        $this->is($w->render('foo'), '-foo-<br /><input type="file" name="foo" id="foo" /><br /><input type="checkbox" name="foo_delete" id="foo_delete" /> <label for="foo_delete">delete</label>', '->render() renders the widget as HTML');

        $this->diag('delete label translation');
        $ws = new sfWidgetFormSchema();
        $ws->addFormFormatter('stub', new FormFormatterStub());
        $ws->setFormFormatterName('stub');
        $w = new sfWidgetFormInputFileEditable(array(
            'file_src' => '-foo-',
        ));
        $w->setParent($ws);
        $this->is($w->render('foo'), '-foo-<br /><input type="file" name="foo" id="foo" /><br /><input type="checkbox" name="foo_delete" id="foo_delete" /> <label for="foo_delete">translation[remove the current file]</label>', '->render() renders the widget as HTML');

        $this->diag('is_image option');
        $w = new sfWidgetFormInputFileEditable(array(
            'file_src' => '-foo-',
            'is_image' => true,
        ));
        $this->is($w->render('foo'), '<img src="-foo-" /><br /><input type="file" name="foo" id="foo" /><br /><input type="checkbox" name="foo_delete" id="foo_delete" /> <label for="foo_delete">remove the current file</label>', '->render() renders the widget as HTML');

        $this->diag('template option');
        $w = new sfWidgetFormInputFileEditable(array(
            'file_src' => '-foo-',
            'template' => '%input%',
        ));
        $this->is($w->render('foo'), '<input type="file" name="foo" id="foo" />', '->render() renders the widget as HTML');
    }
}
