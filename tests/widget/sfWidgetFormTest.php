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
require_once __DIR__.'/../fixtures/MyWidgetForm.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetFormTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        $w = new MyWidgetForm(array('id_format' => '%s'));
        $this->is($w->render('foo'), '<input name="foo" id="foo" /><textarea name="foo" id="foo"></textarea>', '__construct() takes a id_format argument');
        $this->is($w->render('foo', null, array('id' => 'id_foo')), '<input name="foo" id="id_foo" /><textarea name="foo" id="id_foo"></textarea>', '->render() id attributes takes precedence over auto generated ids');

        $w = new MyWidgetForm(array('id_format' => false));
        $this->is($w->render('foo'), '<input name="foo" /><textarea name="foo"></textarea>', '__construct() can disable id generation');

        // ->getLabel() ->setLabel()
        $this->diag('->getLabel() ->setLabel()');
        $w = new MyWidgetForm();
        $this->is($w->getLabel(), null, '->getLabel() returns null if no label has been defined');
        $w = new MyWidgetForm(array('label' => 'foo'));
        $this->is($w->getLabel(), 'foo', '->getLabel() returns the label');
        $w->setLabel('bar');
        $this->is($w->getLabel(), 'bar', '->setLabel() changes the label');

        // ->getDefault() ->setDefault()
        $this->diag('->getDefault() ->setDefault()');
        $w = new MyWidgetForm();
        $this->is($w->getDefault(), null, '->getDefault() returns null if no default value has been defined');
        $w = new MyWidgetForm(array('default' => 'foo'));
        $this->is($w->getDefault(), 'foo', '->getDefault() returns the default value');
        $w->setDefault('bar');
        $this->is($w->getDefault(), 'bar', '->setDefault() changes the default value for the widget');

        // ->getParent() ->setParent()
        $this->diag('->getParent() ->setParent()');
        $w = new MyWidgetForm();
        $this->is($w->getParent(), null, '->getParent() returns null if no widget schema has been defined');
        $w->setParent($ws = new sfWidgetFormSchema());
        $this->is($w->getParent(), $ws, '->setParent() associates a widget schema to the widget');

        // ->getIdFormat() ->setIdFormat()
        $this->diag('->getIdFormat() ->setIdFormat()');
        $w = new MyWidgetForm();
        $w->setIdFormat('id_%s');
        $this->is($w->getIdFormat(), 'id_%s', '->setIdFormat() sets the format for the generated id attribute');

        // ->isHidden()
        $this->diag('->isHidden()');
        $this->is($w->isHidden(), false, '->isHidden() returns false if a widget is not hidden');
        $w->setHidden(true);
        $this->is($w->isHidden(), true, '->isHidden() returns true if a widget is hidden');

        // ->needsMultipartForm()
        $this->diag('->needsMultipartForm()');
        $this->is($w->needsMultipartForm(), false, '->needsMultipartForm() returns false if the widget does not need a multipart form');
        $w = new MyWidgetForm(array('needs_multipart' => true));
        $this->is($w->needsMultipartForm(), true, '->needsMultipartForm() returns false if the widget needs a multipart form');

        // ->renderTag()
        $this->diag('->renderTag()');
        $w = new MyWidgetForm();
        $this->is($w->renderTag('input'), '<input />', '->renderTag() does not add an id if no name is given');
        $this->is($w->renderTag('input', array('id' => 'foo')), '<input id="foo" />', '->renderTag() does not add an id if one is given');
        $this->is($w->renderTag('input', array('name' => 'foo')), '<input name="foo" id="foo" />', '->renderTag() adds an id if none is given and a name is given');
        $w->setIdFormat('id_%s');
        $this->is($w->renderTag('input', array('name' => 'foo')), '<input name="foo" id="id_foo" />', '->renderTag() uses the id_format to generate an id');
        sfWidget::setXhtml(false);
        $this->is($w->renderTag('input'), '<input>', '->renderTag() does not close tag if not in XHTML mode');
        sfWidget::setXhtml(true);

        // ->renderContentTag()
        $this->diag('->renderContentTag()');
        $w = new MyWidgetForm();
        $this->is($w->renderContentTag('textarea'), '<textarea></textarea>', '->renderContentTag() does not add an id if no name is given');
        $this->is($w->renderContentTag('textarea', '', array('id' => 'foo')), '<textarea id="foo"></textarea>', '->renderContentTag() does not add an id if one is given');
        $this->is($w->renderContentTag('textarea', '', array('name' => 'foo')), '<textarea name="foo" id="foo"></textarea>', '->renderContentTag() adds an id if none is given and a name is given');
        $w->setIdFormat('id_%s');
        $this->is($w->renderContentTag('textarea', '', array('name' => 'foo')), '<textarea name="foo" id="id_foo"></textarea>', '->renderContentTag() uses the id_format to generate an id');

        // ->generateId()
        $this->diag('->generateId()');
        $w = new MyWidgetForm();
        $w->setIdFormat('id_for_%s_works');
        $this->is($w->generateId('foo'), 'id_for_foo_works', '->setIdFormat() sets the format of the widget id');
        $this->is($w->generateId('foo[]'), 'id_for_foo_works', '->generateId() removes the [] from the name');
        $this->is($w->generateId('foo[bar][]'), 'id_for_foo_bar_works', '->generateId() replaces [] with _');
        $this->is($w->generateId('foo[bar][]', 'test'), 'id_for_foo_bar_test_works', '->generateId() takes the value into account if provided');
        $this->is($w->generateId('_foo[bar][]', 'test'), 'id_for__foo_bar_test_works', '->generateId() leaves valid ids');

        $w->setIdFormat('id');
        $this->is($w->generateId('foo[bar][]', 'test'), 'foo_bar_test', '->generateId() returns the name if the id format does not contain %s');
        $this->is($w->generateId('foo[bar][]', array('test1', 'test2')), 'foo_bar', '->generateId() ignore the value if not a string');

        $w->setIdFormat('%s');
        $this->is($w->generateId('_foo[bar][]', 'test'), 'foo_bar_test', '->generateId() removes invalid characters');
        $this->is($w->generateId('_foo@bar'), 'foo_bar', '->generateId() removes invalid characters');
        $this->is($w->generateId('_____foo@bar'), 'foo_bar', '->generateId() removes invalid characters');
    }
}
