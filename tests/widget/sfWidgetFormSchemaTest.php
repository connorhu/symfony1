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
require_once __DIR__.'/../fixtures/MyWidget2.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetFormSchemaTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $w1 = new sfWidgetFormInputText(array(), array('class' => 'foo1'));
        $w2 = new sfWidgetFormInputText();

        // __construct()
        $this->diag('__construct()');
        $w = new sfWidgetFormSchema();
        $this->is($w->getFields(), array(), '__construct() can take no argument');
        $w = new sfWidgetFormSchema(array('w1' => $w1, 'w2' => $w2));
        $w1->setParent($w);
        $w2->setParent($w);
        $this->ok($w->getFields() == array('w1' => $w1, 'w2' => $w2), '__construct() can take an array of named sfWidget objects');
        try {
            $w = new sfWidgetFormSchema('string');
            $this->fail('__construct() throws a exception when passing a non supported first argument');
        } catch (InvalidArgumentException $e) {
            $this->pass('__construct() throws an exception when passing a non supported first argument');
        }

        $this->is($w->getFormFormatterName(), 'table', '__construct() sets "form_formatter" option to "table" by default');
        $w = new sfWidgetFormSchema(array(), array('form_formatter' => 'list'));
        $this->is($w->getFormFormatterName(), 'list', '__construct() can override the default value for the "form_formatter" option');

        $this->is($w->getNameFormat(), '%s', '__construct() sets "name_format" option to "table" by default');
        $w = new sfWidgetFormSchema(array(), array('name_format' => 'name_%s'));
        $this->is($w->getNameFormat(), 'name_%s', '__construct() can override the default value for the "name_format" option');

        // implements ArrayAccess
        $this->diag('implements ArrayAccess');
        $w = new sfWidgetFormSchema();
        $w['w1'] = $w1;
        $w['w2'] = $w2;
        $w1->setParent($w);
        $w2->setParent($w);
        $this->ok($w->getFields() == array('w1' => $w1, 'w2' => $w2), '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');
        $this->is($w1->getParent(), $w, 'The widget schema is associated with the fields');
        $this->is($w2->getParent(), $w, 'The widget schema is associated with the fields');

        try {
            $w['w1'] = 'string';
            $this->fail('"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');
        } catch (LogicException $e) {
            $this->pass('"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');
        }

        $w = new sfWidgetFormSchema(array('w1' => $w1));
        $this->is(isset($w['w1']), true, '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');
        $this->is(isset($w['w2']), false, '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');

        $w = new sfWidgetFormSchema(array('w1' => $w1));
        $w1->setParent($w);
        $w2->setParent($w);
        $this->ok($w['w1'] == $w1, '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');
        $this->is($w['w2'], null, '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');

        $w = new sfWidgetFormSchema(array('w1' => $w1, 'w2' => $w2));
        unset($w['w1']);
        $this->is($w['w1'], null, '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');
        $this->is($w->getPositions(), array('w2'), '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');

        // unset with numeric keys
        $w = new sfWidgetFormSchema(array('0' => $w1, 'w2' => $w2));
        unset($w['w2']);
        $this->is($w['w2'], null, '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');
        $this->is($w->getPositions(), array('0'), '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');

        $w = new sfWidgetFormSchema(array('w1' => $w1, '0' => $w2));
        unset($w[0]);
        $this->is($w[0], null, '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');
        $this->is($w->getPositions(), array('w1'), '"sfWidgetFormSchema" implements the ArrayAccess interface for the fields');

        // ->addFormFormatter() ->setFormFormatterName() ->getFormFormatterName() ->getFormFormatter() ->getFormFormatters()
        $this->diag('->addFormFormatter() ->setFormFormatterName() ->getFormFormatterName() ->getFormFormatter() ->getFormFormatters()');
        $w = new sfWidgetFormSchema(array('w1' => $w1));

        $this->is(get_class($w->getFormFormatter()), 'sfWidgetFormSchemaFormatterTable', '->getFormFormatter() returns a sfWidgetSchemaFormatter object');

        $w->addFormFormatter('custom', $customFormatter = new sfWidgetFormSchemaFormatterList($w));
        $w->setFormFormatterName('custom');
        $this->is(get_class($w->getFormFormatter()), 'sfWidgetFormSchemaFormatterList', '->addFormFormatter() associates a name with a sfWidgetSchemaFormatter object');

        $w->setFormFormatterName('list');
        $this->is(get_class($w->getFormFormatter()), 'sfWidgetFormSchemaFormatterList', '->setFormFormatterName() set the names of the formatter to use when rendering');

        $w->setFormFormatterName('nonexistant');
        try {
            $w->getFormFormatter();
            $this->fail('->setFormFormatterName() throws a InvalidArgumentException when the form format name is not associated with a formatter');
        } catch (InvalidArgumentException $e) {
            $this->pass('->setFormFormatterName() throws a InvalidArgumentException when the form format name is not associated with a formatter');
        }

        $formatterNames = array_keys($w->getFormFormatters());
        sort($formatterNames);
        $this->is($formatterNames, array('custom', 'list', 'table'), '->getFormFormatters() returns an array of all formatter for this widget schema');

        // ->setNameFormat() ->getNameFormat() ->generateName()
        $this->diag('->setNameFormat() ->getNameFormat() ->generateName()');
        $w = new sfWidgetFormSchema();
        $this->is($w->generateName('foo'), 'foo', '->generateName() returns a HTML name attribute value for a given field name');
        $w->setNameFormat('article[%s]');
        $this->is($w->generateName('foo'), 'article[foo]', '->setNameFormat() changes the name format');
        $this->is($w->getNameFormat(), 'article[%s]', '->getNameFormat() returns the name format');

        $w->setNameFormat(false);
        $this->is($w->generateName('foo'), 'foo', '->generateName() returns the name unchanged if the format is false');

        try {
            $w->setNameFormat('foo');
            $this->fail('->setNameFormat() throws an InvalidArgumentException if the format does not contain %s');
        } catch (InvalidArgumentException $e) {
            $this->pass('->setNameFormat() throws an InvalidArgumentException if the format does not contain %s');
        }

        $w = new sfWidgetFormSchema(array(
            'author' => new sfWidgetFormSchema(array(
                'first_name' => new sfWidgetFormInputText(),
                'company' => new sfWidgetFormSchema(array(
                    'name' => new sfWidgetFormInputText(),
                )),
            )),
        ));
        $w->setNameFormat('article[%s]');
        $this->is($w['author']->generateName('first_name'), 'article[author][first_name]', '->generateName() returns a HTML name attribute value for a given field name');
        $this->is($w['author']['company']->generateName('name'), 'article[author][company][name]', '->generateName() returns a HTML name attribute value for a given field name');

        // ->getParent() ->setParent()
        $this->diag('->getParent() ->setParent()');
        $author = new sfWidgetFormSchema(array('first_name' => new sfWidgetFormInputText()));
        $company = new sfWidgetFormSchema(array('name' => new sfWidgetFormInputText()));
        $this->is($company->getParent(), null, '->getParent() returns null if there is no parent widget schema');
        $company->setParent($author);
        $this->is($company->getParent(), $author, '->getParent() returns the parent widget schema');

        // ->setLabels() ->setLabel() ->getLabels() ->getLabel() ->generateLabelName()
        $this->diag('->setLabels() ->setLabel() ->getLabels() ->getLabel() ->generateLabelName()');
        $w = new sfWidgetFormSchema(array('first_name' => new sfWidgetFormInputText()));
        $w->setLabel('first_name', 'A first name');
        $this->is($w->getLabels(), array('first_name' => 'A first name'), '->getLabels() returns all current labels');

        $w->setLabels(array('first_name' => 'The first name'));
        $this->is($w->getFormFormatter()->generateLabelName('first_name'), 'The first name', '->setLabels() changes all current labels');

        $w->setLabel('first_name', 'A first name');
        $this->is($w->getFormFormatter()->generateLabelName('first_name'), 'A first name', '->setLabel() sets a label value');

        // ->setHelps() ->getHelps() ->setHelp() ->getHelp()
        $this->diag('->setHelps() ->getHelps() ->setHelp() ->getHelp()');
        $w = new sfWidgetFormSchema();
        $w->setHelps(array('first_name', 'Please, provide your first name'));
        $this->is($w->getHelps(), array('first_name', 'Please, provide your first name'), '->setHelps() changes all help messages');
        $w->setHelp('last_name', 'Please, provide your last name');
        $this->is($w->getHelp('last_name'), 'Please, provide your last name', '->setHelp() changes one help message');

        // ->getLabel() ->setLabel() ->getLabels() ->setLabels()
        $this->diag('->getLabel() ->setLabel() ->getLabels() ->setLabels()');
        $w = new sfWidgetFormSchema(array('w1' => $w1, 'w2' => $w2));
        $w->setLabels(array('w1' => 'foo'));
        $this->is($w->getLabels(), array('w1' => 'foo', 'w2' => null), '->getLabels() returns the labels');
        $this->is($w->getLabel('w1'), 'foo', '->getLabel() returns the label for a given field');
        $w->setLabel('w2', 'foo');
        $this->is($w->getLabels(), array('w1' => 'foo', 'w2' => 'foo'), '->setLabel() sets a label for a given field');
        $w->setLabel('foo');
        $this->is($w->getLabel(), 'foo', '->setLabel() can also set the label for the widget schema');

        // ->getDefault() ->setDefault() ->getDefaults() ->setDefaults()
        $this->diag('->getDefault() ->setDefault() ->getDefaults() ->setDefaults()');
        $w = new sfWidgetFormSchema(array('w1' => $w1, 'w2' => $w2));
        $w->setDefaults(array('w1' => 'foo'));
        $this->is($w->getDefaults(), array('w1' => 'foo', 'w2' => null), '->getDefaults() returns the default values');
        $this->is($w->getDefault('w1'), 'foo', '->getDefault() returns the default value for a given field');
        $w->setDefault('w2', 'foo');
        $this->is($w->getDefaults(), array('w1' => 'foo', 'w2' => 'foo'), '->setDefault() sets a default value for a given field');

        // ->needsMultipartForm()
        $this->diag('->needsMultipartForm()');
        $w = new sfWidgetFormSchema(array('w1' => $w1));
        $this->is($w->needsMultipartForm(), false, '->needsMultipartForm() returns false if the form schema does not have a widget that needs a multipart form');
        $w['w2'] = new sfWidgetFormInputFile();
        $this->is($w->needsMultipartForm(), true, '->needsMultipartForm() returns true if the form schema does not have a widget that needs a multipart form');

        // ->renderField()
        $this->diag('->renderField()');
        $w = new sfWidgetFormSchema(array('first_name' => $w1));
        $this->is($w->renderField('first_name', 'Fabien'), '<input class="foo1" type="text" name="first_name" value="Fabien" id="first_name" />', '->renderField() renders a field to HTML');

        $ww = clone $w1;
        $ww->setAttribute('id', 'foo');
        $ww->setAttribute('style', 'color: blue');
        $w = new sfWidgetFormSchema(array('first_name' => $ww));
        $this->is($w->renderField('first_name', 'Fabien'), '<input class="foo1" id="foo" style="color: blue" type="text" name="first_name" value="Fabien" />', '->renderField() renders a field to HTML');

        try {
            $w->renderField('last_name', 'Potencier');
            $this->fail('->renderField() throws an InvalidArgumentException if the field does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->renderField() throws an InvalidArgumentException if the field does not exist');
        }

        // ->setPositions() ->getPositions()
        $this->diag('->setPositions() ->getPositions()');
        $w = new sfWidgetFormSchema();
        $w['w1'] = $w1;
        $w['w2'] = $w2;
        $w->setPositions(array('w2', 'w1'));
        $this->is($w->getPositions(), array('w2', 'w1'), '->setPositions() changes all field positions');
        $w->setPositions(array('w1', 'w2'));
        $this->is($w->getPositions(), array('w1', 'w2'), '->setPositions() changes all field positions');

        $w = new sfWidgetFormSchema();
        $w['w1'] = $w1;
        $w['w2'] = $w2;
        $w['w1'] = $w1;
        $this->is($w->getPositions(), array('w1', 'w2'), '->setPositions() changes all field positions');

        $w = new sfWidgetFormSchema();
        $w['w1'] = $w1;
        $w['w2'] = $w2;
        $w->setPositions(array('w1', 'w2', 'w1'));
        $this->is($w->getPositions(), array('w1', 'w2'), '->setPositions() does not repeat the fields');

        try {
            $w->setPositions(array('w1', 'w2', 'w3'));
            $this->fail('->setPositions() throws an InvalidArgumentException if you give it a non existant field name');
        } catch (InvalidArgumentException $e) {
            $this->pass('->setPositions() throws an InvalidArgumentException if you give it a non existant field name');
        }

        try {
            $w->setPositions(array('w1'));
            $this->fail('->setPositions() throws an InvalidArgumentException if you miss a field name');
        } catch (InvalidArgumentException $e) {
            $this->pass('->setPositions() throws an InvalidArgumentException if you miss a field name');
        }

        // ->moveField()
        $this->diag('->moveField()');
        $w = new sfWidgetFormSchema();
        $w['w1'] = $w1;
        $w['w2'] = $w2;
        $w['w3'] = $w1;
        $w['w4'] = $w2;
        $w->moveField('w1', sfWidgetFormSchema::BEFORE, 'w3');
        $this->is($w->getPositions(), array('w2', 'w1', 'w3', 'w4'), '->moveField() can move a field before another one');
        $w->moveField('w1', sfWidgetFormSchema::LAST);
        $this->is($w->getPositions(), array('w2', 'w3', 'w4', 'w1'), '->moveField() can move a field to the end');
        $w->moveField('w1', sfWidgetFormSchema::FIRST);
        $this->is($w->getPositions(), array('w1', 'w2', 'w3', 'w4'), '->moveField() can move a field to the beginning');
        $w->moveField('w1', sfWidgetFormSchema::AFTER, 'w3');
        $this->is($w->getPositions(), array('w2', 'w3', 'w1', 'w4'), '->moveField() can move a field before another one');
        try {
            $w->moveField('w1', sfWidgetFormSchema::AFTER);
            $this->fail('->moveField() throws an LogicException if you don\'t pass a relative field name with AFTER');
        } catch (LogicException $e) {
            $this->pass('->moveField() throws an LogicException if you don\'t pass a relative field name with AFTER');
        }
        try {
            $w->moveField('w1', sfWidgetFormSchema::BEFORE);
            $this->fail('->moveField() throws an LogicException if you don\'t pass a relative field name with BEFORE');
        } catch (LogicException $e) {
            $this->pass('->moveField() throws an LogicException if you don\'t pass a relative field name with BEFORE');
        }
        // this case is especially interesting because the numeric array keys are always
        // converted to integers by array
        // furthermore, (int)0 == (string)'w1' succeeds
        $w = new sfWidgetFormSchema(array('w1' => $w1, '0' => $w2));
        $w->moveField(0, sfWidgetFormSchema::FIRST);
        $this->is($w->getPositions(), array('0', 'w1'), '->moveField() compares field names as strings');

        $w = new sfWidgetFormSchema(array('w1' => $w1, '0' => $w2));
        $w->moveField('0', sfWidgetFormSchema::FIRST);
        $this->is($w->getPositions(), array('0', 'w1'), '->moveField() compares field names as strings');

        $w = new sfWidgetFormSchema(array('w1' => $w1, 'w2' => $w2, '0' => $w1));
        $w->moveField('w1', sfWidgetFormSchema::BEFORE, '0');
        $this->is($w->getPositions(), array('w2', 'w1', '0'), '->moveField() compares field names as strings');

        $w = new sfWidgetFormSchema(array('w1' => $w1, 'w2' => $w2, '0' => $w1));
        $w->moveField('w1', sfWidgetFormSchema::BEFORE, 0);
        $this->is($w->getPositions(), array('w2', 'w1', '0'), '->moveField() compares field names as strings');

        // ->getGlobalErrors()
        $this->diag('->getGlobalErrors()');
        $w = new sfWidgetFormSchema();
        $w['w1'] = $w1;
        $w['w2'] = new sfWidgetFormInputHidden();
        $w['w3'] = new sfWidgetFormSchema();
        $w['w3']['w1'] = $w1;
        $w['w3']['w2'] = new sfWidgetFormInputHidden();
        $errors = array(
            'global error',
            'w1' => 'error for w1',
            'w2' => 'error for w2',
            'w4' => array(
                'w1' => 'error for w4/w1',
                'w2' => 'error for w4/w2',
                'w3' => 'error for w4/w3',
            ),
            'w4' => 'error for w4',
        );
        $this->is($w->getGlobalErrors($errors), array('global error', 'error for w4', 'W2' => 'error for w2'), '->getGlobalErrors() returns an array of global errors, errors for hidden fields, and errors for non existent fields');

        // ->render()
        $this->diag('->render()');
        $w = new sfWidgetFormSchema();

        try {
            $w->render(null, 'string');
            $this->fail('->render() throws an InvalidArgumentException if the second argument is not an array');
        } catch (InvalidArgumentException $e) {
            $this->pass('->render() throws an InvalidArgumentException if the second argument is not an array');
        }

        $w['first_name'] = $w1;
        $w['last_name'] = $w2;
        $w['id'] = new sfWidgetFormInputHidden();
        $w->setAttribute('style', 'padding: 5px');
        $w->setNameFormat('article[%s]');
        $w->setIdFormat('id_%s');
        $expected = <<<'EOF'
        <tr><td colspan="2">
          <ul class="error_list">
            <li>Global error message</li>
            <li>Id: Required</li>
          </ul>
        </td></tr>
        <tr>
          <th><label style="padding: 5px" for="id_article_first_name">First name</label></th>
          <td>  <ul class="error_list">
            <li>Too short</li>
          </ul>
        <input class="foo" type="text" name="article[first_name]" value="Fabien" id="id_article_first_name" /></td>
        </tr>
        <tr>
          <th><label style="padding: 5px" for="id_article_last_name">Last name</label></th>
          <td><input type="text" name="article[last_name]" value="Potencier" class="bar" id="id_article_last_name" /><input type="hidden" name="article[id]" id="id_article_id" /></td>
        </tr>
        
        EOF;
        $rendered = $w->render(null, array('first_name' => 'Fabien', 'last_name' => 'Potencier'), array('first_name' => array('class' => 'foo'), 'last_name' => array('class' => 'bar')), array('first_name' => 'Too short', 'Global error message', 'id' => 'Required'));
        $this->is($rendered, fix_linebreaks($expected), '->render() renders a schema to HTML');

        $this->diag('Widget schema with only hidden fields');
        $w = new sfWidgetFormSchema(array('w1' => new sfWidgetFormInputHidden()));
        $this->is($w->render(null), '<input type="hidden" name="w1" id="w1" />', '->render() is able to render widget schema that only contains hidden fields');

        $this->diag('Widget schema with an embed form as the last field and hidden fields');
        $w = new sfWidgetFormSchema();
        $w['w1'] = new sfWidgetFormInputHidden();
        $ew = new sfWidgetFormSchema(array('w3' => new sfWidgetFormInputText()));
        $w['w4'] = new sfWidgetFormSchemaDecorator($ew, $w->getFormFormatter()->getDecoratorFormat());
        $expected = <<<'EOF'
        <tr>
          <th>W4</th>
          <td>
            <table>
              <tr>
                <th><label for="w4_w3">W3</label></th>
                <td><input type="text" name="w4[w3]" id="w4_w3" /></td>
              </tr>
            </table>
            <input type="hidden" name="w1" id="w1" />
          </td>
        </tr>
        
        EOF;
        $this->is(str_replace("\n", '', preg_replace('/^ +/m', '', $w->render(null))), str_replace("\n", '', preg_replace('/^ +/m', '', fix_linebreaks($expected))), '->render() is able to render widget schema that only contains hidden fields when the last field is a form');

        // __clone()
        $this->diag('__clone()');
        $w = new sfWidgetFormSchema(array('w1' => $w1, 'w2' => $w2));
        $w1->setParent($w);
        $w2->setParent($w);
        $format1 = new sfWidgetFormSchemaFormatterList($w);
        $format1->setTranslationCatalogue('english');
        $w->addFormFormatter('testFormatter', $format1);
        $w1 = clone $w;
        $f1 = $w1->getFields();
        $f = $w->getFields();
        $this->is(array_keys($f1), array_keys($f), '__clone() clones embedded widgets');
        foreach ($f1 as $name => $widget) {
            $this->ok($widget !== $f[$name], '__clone() clones embedded widgets');
            $this->ok($widget->getParent() === $w1, 'The parents hafe been changed');
            // avoid recursive dependencies at comparing
            $widget->setParent(null);
            $f[$name]->setParent(null);
            $this->ok($widget == $f[$name], '__clone() clones embedded widgets');
        }
        $format1->setTranslationCatalogue('french');
        $formatters = $w1->getFormFormatters();
        $this->is(count($formatters), 1, '__clone() returns a sfWidgetFormSchema that has the Formatters attached');
        $this->is($formatters['testFormatter']->getTranslationCatalogue(), 'english', '__clone() clones formatters, so that changes to the original one have no effect to the cloned formatter.');

        $w = new sfWidgetFormSchema();
        $w->addFormFormatter('table', new sfWidgetFormSchemaFormatterTable($w));
        $w->addFormFormatter('list', new sfWidgetFormSchemaFormatterList($w));
        $w1 = clone $w;
        $f1 = $w1->getFormFormatters();
        $f = $w->getFormFormatters();
        $this->is(array_keys($f1), array_keys($f), '__clone() clones form formatters');
        foreach ($f1 as $key => $formFormatter) {
            $this->ok($formFormatter !== $f[$key], '__clone() clones form formatters');
            $this->is(get_class($formFormatter), get_class($f[$key]), '__clone() clones form formatters');

            $this->ok($formFormatter->getWidgetSchema() !== $f[$key]->getWidgetSchema(), '__clone() clones form formatters');
            $this->is(get_class($formFormatter->getWidgetSchema()), get_class($f[$key]->getWidgetSchema()), '__clone() clones form formatters');
        }

        // setDefaultFormFormatterName()
        $this->diag('setDefaultFormFormatterName()');
        $w = new sfWidgetFormSchema(array('w1' => $w1, 'w2' => $w2));
        $this->isa_ok($w->getFormFormatter(), 'sfWidgetFormSchemaFormatterTable', 'setDefaultFormFormatterName() has the "sfWidgetFormSchemaFormatterTable" form formatter by default');

        sfWidgetFormSchema::setDefaultFormFormatterName('list');
        $w = new sfWidgetFormSchema(array('w1' => $w1, 'w2' => $w2));
        $this->isa_ok($w->getFormFormatter(), 'sfWidgetFormSchemaFormatterList', 'setDefaultFormFormatterName() changes the default form formatter name correctly');

        // ->getJavaScripts() ->getStylesheets()
        $this->diag('->getJavaScripts() ->getStylesheets()');
        $w = new sfWidgetFormSchema(array(
            'foo' => new MyWidget2(array('name' => 'foo')),
            'bar' => new MyWidget2(array('name' => 'bar')),
        ));
        $this->is($w->getJavaScripts(), array('/path/to/a/foo.js', '/path/to/foo.js', '/path/to/a/bar.js'), '->getJavaScripts() returns an array of stylesheets');
        $this->is($w->getStylesheets(), array('/path/to/a/foo.css' => 'all', '/path/to/foo.css' => 'all', '/path/to/a/bar.css' => 'all'), '->getStylesheets() returns an array of JavaScripts');
    }
}
