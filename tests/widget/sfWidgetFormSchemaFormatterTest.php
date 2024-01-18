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
require_once __DIR__.'/../fixtures/MyFormatter.php';
require_once __DIR__.'/../fixtures/MyFormatter2.php';
require_once __DIR__.'/../fixtures/myI18n.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetFormSchemaFormatterTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $w1 = new sfWidgetFormInputText();
        $w2 = new sfWidgetFormInputText();
        $w = new sfWidgetFormSchema(array('w1' => $w1, 'w2' => $w2));
        $f = new MyFormatter($w);

        // ->formatRow()
        $this->diag('->formatRow()');
        $output = <<<'EOF'
        <li>
          <label>label</label>
          <input /><p>help</p>
        </li>
        
        EOF;
        $this->is($f->formatRow('<label>label</label>', '<input />', array(), '<p>help</p>', ''), fix_linebreaks($output), '->formatRow() formats a field in a row');

        // ->formatErrorRow()
        $this->diag('->formatErrorRow()');
        $output = <<<'EOF'
        <li>
          <ul class="error_list">
            <li>Global error</li>
            <li>id: required</li>
            <li>1 > sub_id: required</li>
          </ul>
        </li>
        
        EOF;
        $this->is($f->formatErrorRow(array('Global error', 'id' => 'required', array('sub_id' => 'required'))), fix_linebreaks($output), '->formatErrorRow() formats an array of errors in a row');

        // ->unnestErrors()
        $this->diag('->unnestErrors()');
        $f->setErrorRowFormatInARow('<li>%error%</li>');
        $f->setNamedErrorRowFormatInARow('<li>%name%: %error%</li>');
        $errors = array('foo', 'bar', 'foobar' => 'foobar');
        $this->is($f->unnestErrors($errors), array('<li>foo</li>', '<li>bar</li>', '<li>foobar: foobar</li>'), '->unnestErrors() returns an array of formatted errors');
        $errors = array('foo', 'bar' => array('foo', 'foobar' => 'foobar'));
        $this->is($f->unnestErrors($errors), array('<li>foo</li>', '<li>foo</li>', '<li>bar > foobar: foobar</li>'), '->unnestErrors() unnests errors');

        foreach (array('RowFormat', 'ErrorRowFormat', 'ErrorListFormatInARow', 'ErrorRowFormatInARow', 'NamedErrorRowFormatInARow', 'DecoratorFormat') as $method) {
            $getter = sprintf('get%s', $method);
            $setter = sprintf('set%s', $method);
            $this->diag(sprintf('->%s() ->%s()', $getter, $setter));
            $f->{$setter}($value = rand(1, 99999));
            $this->is($f->{$getter}(), $value, sprintf('->%s() ->%s()', $getter, $setter));
        }

        $this->diag('::setTranslationCallable() ::getTranslationCallable()');
        function my__($string)
        {
            return sprintf('[%s]', $string);
        }

        MyFormatter::setTranslationCallable('my__');

        $this->is(MyFormatter::getTranslationCallable(), 'my__', 'get18nCallable() retrieves i18n callable correctly');

        MyFormatter::setTranslationCallable(new sfCallable('my__'));
        $this->isa_ok(MyFormatter::getTranslationCallable(), 'sfCallable', 'get18nCallable() retrieves i18n sfCallable correctly');

        try {
            $f->setTranslationCallable('foo');
            $this->fail('setTranslationCallable() does not throw InvalidException when i18n callable is invalid');
        } catch (InvalidArgumentException $e) {
            $this->pass('setTranslationCallable() throws InvalidException if i18n callable is not a valid callable');
        } catch (Exception $e) {
            $this->fail('setTranslationCallable() throws unexpected exception');
        }

        $this->diag('->translate()');
        $f = new MyFormatter(new sfWidgetFormSchema());
        $this->is($f->translate('label'), '[label]', 'translate() call i18n sfCallable as expected');

        MyFormatter::setTranslationCallable(array('myI18n', '__'));
        $this->is($f->translate('label'), '[label]', 'translate() call i18n callable as expected');

        $this->diag('->generateLabel() ->generateLabelName() ->setLabel() ->setLabels()');
        MyFormatter::dropTranslationCallable();
        $w = new sfWidgetFormSchema(array(
            'author_id' => new sfWidgetFormInputText(),
            'first_name' => new sfWidgetFormInputText(),
            'last_name' => new sfWidgetFormInputText(),
        ));
        $f = new MyFormatter($w);
        $this->is($f->generateLabelName('first_name'), 'First name', '->generateLabelName() generates a label value from a label name');
        $this->is($f->generateLabelName('author_id'), 'Author', '->generateLabelName() removes _id from auto-generated labels');

        $w->setLabels(array('first_name' => 'The first name'));
        $this->is($f->generateLabelName('first_name'), 'The first name', '->setLabels() changes all current labels');

        $w->setLabel('first_name', 'A first name');
        $this->is($f->generateLabelName('first_name'), 'A first name', '->setLabel() sets a label value');

        $w->setLabel('first_name', false);
        $this->is($f->generateLabel('first_name'), '', '->generateLabel() returns an empty string if the label is false');

        $w->setLabel('first_name', 'Your First Name');
        $this->is($f->generateLabel('first_name'), '<label for="first_name">Your First Name</label>', '->generateLabelName() returns a label tag');
        $this->is($f->generateLabel('first_name', array('class' => 'foo')), '<label class="foo" for="first_name">Your First Name</label>', '->generateLabelName() returns a label tag with optional HTML attributes');
        $this->is($f->generateLabel('first_name', array('for' => 'myid')), '<label for="myid">Your First Name</label>', '->generateLabelName() returns a label tag with specified for-id');

        $w->setLabel('last_name', 'Your Last Name');
        $this->is($f->generateLabel('last_name'), '<label for="last_name">Your Last Name</label>', '->generateLabelName() returns a label tag');
        MyFormatter::setTranslationCallable('my__');
        $this->is($f->generateLabel('last_name'), '<label for="last_name">[Your Last Name]</label>', '->generateLabelName() returns a i18ned label tag');

        // ->setTranslationCatalogue() ->getTranslationCatalogue()

        $f = new MyFormatter2(new sfWidgetFormSchema(array()));
        $f->setTranslationCatalogue('foo');
        $this->is($f->getTranslationCatalogue(), 'foo', 'setTranslationCatalogue() has set the i18n catalogue correctly');
        $this->diag('->setTranslationCatalogue() ->getTranslationCatalogue()');
        try {
            $f->setTranslationCatalogue(array('foo'));
            $this->fail('setTranslationCatalogue() does not throw an exception when catalogue name is incorrectly typed');
        } catch (InvalidArgumentException $e) {
            $this->pass('setTranslationCatalogue() throws an exception when catalogue name is incorrectly typed');
        }

        function ___my($s, $p, $c)
        {
            return $c;
        }

        $f = new MyFormatter2(new sfWidgetFormSchema());
        $f->setTranslationCallable('___my');
        $f->setTranslationCatalogue('bar');
        $this->is($f->translate('foo', array()), 'bar', 'translate() passes back the catalogue to the translation callable');

        // reset callable
        MyFormatter::dropTranslationCallable();
    }
}
