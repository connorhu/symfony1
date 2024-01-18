<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/FormTest.php';
require_once __DIR__.'/../fixtures/MyWidget.php';
require_once __DIR__.'/../fixtures/TestForm1.php';
require_once __DIR__.'/../fixtures/TestForm2.php';
require_once __DIR__.'/../fixtures/TestForm3.php';
require_once __DIR__.'/../fixtures/TestForm4.php';
require_once __DIR__.'/../fixtures/NumericFieldsForm.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfFormTest extends TestCase
{
    public function setUp(): void
    {
        sfForm::disableCSRFProtection();
    }

    public function testConstruct()
    {
        $form = new FormTest();
        $this->assertSame(true, $form->getValidatorSchema() instanceof sfValidatorSchema, '__construct() creates an empty validator schema');
        $this->assertSame(true, $form->getWidgetSchema() instanceof sfWidgetFormSchema, '__construct() creates an empty widget form schema');

        $form = new sfForm(array('first_name' => 'Fabien'));
        $this->assertSame(array('first_name' => 'Fabien'), $form->getDefaults(), '__construct() can take an array of default values as its first argument');

        $form = new FormTest(array(), array(), 'secret');
        $v = $form->getValidatorSchema();
        $this->assertSame(true, $form->isCSRFProtected(), '__construct() takes a CSRF secret as its second argument');
        $this->assertSame('*secret*', $v[sfForm::getCSRFFieldName()]->getOption('token'), '__construct() takes a CSRF secret as its second argument');

        sfForm::enableCSRFProtection();
        $form = new FormTest(array(), array(), false);
        $this->assertSame(true, !$form->isCSRFProtected(), '__construct() can disable the CSRF protection by passing false as the second argument');

        $form = new FormTest();
        $this->assertSame(true, $form->isCSRFProtected(), '__construct() uses CSRF protection if null is passed as the second argument and it\'s enabled globally');
    }

    public function testOption()
    {
        $test = new FormTest(array(), array('foo' => 'bar'));
        $this->assertSame('bar', $test->getOption('foo'), '__construct takes an option array as its second argument');
        $test->setOption('bar', 'foo');
        $this->assertSame('foo', $test->getOption('bar'), '->setOption() changes the value of an option');
        $this->assertSame(array('foo' => 'bar', 'bar' => 'foo'), $test->getOptions(), '->getOptions() returns all options');
    }

    public function testDefault()
    {
        $form = new FormTest();
        $form->setDefaults(array('first_name' => 'Fabien'));
        $this->assertSame(array('first_name' => 'Fabien'), $form->getDefaults(), 'setDefaults() sets the form default values');

        $form->setDefault('last_name', 'Potencier');
        $this->assertSame(array('first_name' => 'Fabien', 'last_name' => 'Potencier'), $form->getDefaults(), 'setDefault() sets a default value');
        $this->assertSame(true, $form->hasDefault('first_name'), 'hasDefault() returns true if the form has a default value for the given field');
        $this->assertSame(false, $form->hasDefault('name'), 'hasDefault() returns false if the form does not have a default value for the given field');
        $this->assertSame('Fabien', $form->getDefault('first_name'), 'getDefault() returns a default value for a given field');
        $this->assertSame(null, $form->getDefault('name'), 'getDefault() returns null if the form does not have a default value for a given field');

        sfForm::enableCSRFProtection('*mygreatsecret*');
        $form = new FormTest();
        $form->setDefaults(array('first_name' => 'Fabien'));
        $this->assertSame($form->getDefault('_csrf_token'), $form->getCSRFToken('*mygreatsecret*'), '->getDefaults() keeps the CSRF token default value');

        $form = new FormTest(array(), array(), false);
        $form->setDefaults(array('first_name' => 'Fabien'));
        $this->assertSame(false, array_key_exists('_csrf_token', $form->getDefaults()), '->setDefaults() does not set the CSRF token if CSRF is disabled');
        sfForm::disableCSRFProtection();
    }

    public function testName()
    {
        $form = new FormTest();
        $w = new sfWidgetFormSchema();
        $form->setWidgetSchema($w);

        $this->assertSame(true, false === $form->getName(), '->getName() returns false if the name format is not an array');
        $w->setNameFormat('foo_%s');
        $this->assertSame(true, false === $form->getName(), '->getName() returns false if the name format is not an array');
        $w->setNameFormat('foo[%s]');
        $this->assertSame('foo', $form->getName(), '->getName() returns the name under which user data can be retrieved');
    }

    public function testCSRFProtection()
    {
        sfForm::enableCSRFProtection();
        $f1 = new FormTest();
        $this->assertSame(true, $f1->isCSRFProtected(), '::enableCSRFProtection() enabled CSRF protection for all future forms');
        sfForm::disableCSRFProtection();
        $f2 = new FormTest();
        $this->assertSame(true, !$f2->isCSRFProtected(), '::disableCSRFProtection() disables CSRF protection for all future forms');
        $this->assertSame(true, $f1->isCSRFProtected(), '::enableCSRFProtection() enabled CSRF protection for all future forms');
        sfForm::enableCSRFProtection();
        $this->assertSame(true, !$f2->isCSRFProtected(), '::disableCSRFProtection() disables CSRF protection for all future forms');

        $form = new FormTest(array(), array(), false);
        $this->assertSame(true, !$form->isCSRFProtected(), '->isCSRFProtected() returns true if the form is CSRF protected');

        sfForm::enableCSRFProtection('mygreatsecret');
        $form = new FormTest();
        $v = $form->getValidatorSchema();
        $this->assertSame('*mygreatsecret*', $v[sfForm::getCSRFFieldName()]->getOption('token'), '::enableCSRFProtection() can take a secret argument');
    }

    public function testLocalCSRFProtection()
    {
        $form = new TestForm3();
        sfForm::disableCSRFProtection();
        $this->assertSame(true, !$form->isCSRFProtected(), '->disableLocalCSRFProtection() disabled CSRF protection for the current form');
        sfForm::enableCSRFProtection();
        $this->assertSame(true, !$form->isCSRFProtected(), '->disableLocalCSRFProtection() disabled CSRF protection for the current form, even if the global CSRF protection is enabled');

        $form = new TestForm3(array(), array(), 'foo');
        $this->assertSame(true, !$form->isCSRFProtected(), '->disableLocalCSRFProtection() disabled CSRF protection for the current form, even a CSRF secret is provided in the constructor');
        sfForm::disableCSRFProtection();

        $form = new TestForm4();
        $this->assertSame(true, $form->isCSRFProtected(), '->enableLocalCSRFProtection() enables CSRF protection when passed null and global CSRF is disabled');

        $form = new TestForm4(array(), array('csrf_secret' => '**localsecret**'));
        $this->assertSame(true, $form->isCSRFProtected(), '->enableLocalCSRFProtection() enables CSRF protection when passed a string global CSRF is disabled');
    }

    public function testCSRFFieldName()
    {
        sfForm::enableCSRFProtection();
        sfForm::setCSRFFieldName('_token_');
        $f = new FormTest();
        $v = $f->getValidatorSchema();
        $this->assertSame(true, isset($v['_token_']), '::setCSRFFieldName() changes the CSRF token field name');
        $this->assertSame('_token_', sfForm::getCSRFFieldName(), '::getCSRFFieldName() returns the CSRF token field name');
    }

    public function testMultipart()
    {
        $form = new FormTest();
        $this->assertSame(true, !$form->isMultipart(), '->isMultipart() returns false if the form does not need a multipart form');
        $form->setWidgetSchema(new sfWidgetFormSchema(array('image' => new sfWidgetFormInputFile())));
        $this->assertSame(true, $form->isMultipart(), '->isMultipart() returns true if the form needs a multipart form');
    }

    public function testValidators()
    {
        $form = new FormTest();
        $validators = array(
            'first_name' => new sfValidatorPass(),
            'last_name' => new sfValidatorPass(),
        );
        $validatorSchema = new sfValidatorSchema($validators);
        $form->setValidatorSchema($validatorSchema);
        $this->assertSame($validatorSchema, $form->getValidatorSchema(), '->setValidatorSchema() sets the current validator schema');

        $form->setValidators($validators);
        $schema = $form->getValidatorSchema();
        $this->assertSame(true, $schema['first_name'] == $validators['first_name'], '->setValidators() sets field validators');
        $this->assertSame(true, $schema['last_name'] == $validators['last_name'], '->setValidators() sets field validators');
        $form->setValidator('name', $v3 = new sfValidatorPass());
        $this->assertSame(true, $form->getValidator('name') == $v3, '->setValidator() sets a validator for a field');
    }

    public function testWidgets()
    {
        $form = new FormTest();
        $widgets = array(
            'first_name' => new sfWidgetFormInputText(),
            'last_name' => new sfWidgetFormInputText(),
        );
        $widgetSchema = new sfWidgetFormSchema($widgets);
        $form->setWidgetSchema($widgetSchema);
        $this->assertSame(true, $form->getWidgetSchema() == $widgetSchema, '->setWidgetSchema() sets the current widget schema');
        $form->setWidgets($widgets);
        $schema = $form->getWidgetSchema();
        $widgets['first_name']->setParent($schema);
        $widgets['last_name']->setParent($schema);
        $this->assertSame(true, $schema['first_name'] == $widgets['first_name'], '->setWidgets() sets field widgets');
        $this->assertSame(true, $schema['last_name'] == $widgets['last_name'], '->setWidgets() sets field widgets');

        $w3 = new sfWidgetFormInputText();
        $form->setWidget('name', $w3);
        $w3->setParent($schema);
        $this->assertSame(true, $form->getWidget('name') == $w3, '->setWidget() sets a widget for a field');
    }

    public function testArrayAccess()
    {
        $form = new FormTest();
        $form->setWidgetSchema(new sfWidgetFormSchema(array(
            'first_name' => new sfWidgetFormInputText(array('default' => 'Fabien')),
            'last_name' => new sfWidgetFormInputText(),
            'image' => new sfWidgetFormInputFile(),
        )));
        $form->setValidatorSchema(new sfValidatorSchema(array(
            'first_name' => new sfValidatorPass(),
            'last_name' => new sfValidatorPass(),
            'image' => new sfValidatorPass(),
        )));
        $form->setDefaults(array(
            'image' => 'default.gif',
        ));
        $form->embedForm('embedded', new sfForm());
        $this->assertSame(true, $form['first_name'] instanceof sfFormField, '"sfForm" implements the ArrayAccess interface');
        $this->assertSame('<input type="text" name="first_name" value="Fabien" id="first_name" />', $form['first_name']->render(), '"sfForm" implements the ArrayAccess interface');

        try {
            $form['image'] = 'image';

            $this->assertSame(true, false, '"sfForm" ArrayAccess implementation does not permit to set a form field');
        } catch (LogicException $e) {
            $this->assertInstanceOf(LogicException::class, $e);
        }

        $this->assertSame(true, isset($form['image']), '"sfForm" implements the ArrayAccess interface');
        unset($form['image']);

        $this->assertSame(true, !isset($form['image']), '"sfForm" implements the ArrayAccess interface');
        $this->assertSame(true, !array_key_exists('image', $form->getDefaults()), '"sfForm" ArrayAccess implementation removes form defaults');
        $v = $form->getValidatorSchema();
        $this->assertSame(true, !isset($v['image']), '"sfForm" ArrayAccess implementation removes the widget and the validator');
        $w = $form->getWidgetSchema();
        $this->assertSame(true, !isset($w['image']), '"sfForm" ArrayAccess implementation removes the widget and the validator');

        try {
            $form['nonexistant'];

            $this->assertSame(true, false, '"sfForm" ArrayAccess implementation throws a LogicException if the form field does not exist');
        } catch (LogicException $e) {
            $this->assertInstanceOf(LogicException::class, $e);
        }

        unset($form['embedded']);
        $this->assertSame(true, !array_key_exists('embedded', $form->getEmbeddedForms()), '"sfForm" ArrayAccess implementation removes embedded forms');

        $form->bind(array(
            'first_name' => 'John',
            'last_name' => 'Doe',
        ));
        unset($form['first_name']);
        $this->assertSame(array('last_name' => 'Doe'), $form->getValues(), '"sfForm" ArrayAccess implementation removes bound values');

        $w['first_name'] = new sfWidgetFormInputText();
        $this->assertSame(null, $form['first_name']->getValue(), '"sfForm" ArrayAccess implementation removes tainted values');
    }

    public function testCountable()
    {
        $form = new FormTest();
        $form->setWidgetSchema(new sfWidgetFormSchema(array(
            'first_name' => new sfWidgetFormInputText(array('default' => 'Fabien')),
            'last_name' => new sfWidgetFormInputText(),
            'image' => new sfWidgetFormInputFile(),
        )));

        $this->assertSame(3, count($form), '"sfForm" implements the Countable interface');
    }

    public function testIterator()
    {
        $form = new FormTest();
        $form->setWidgetSchema(new sfWidgetFormSchema(array(
            'first_name' => new sfWidgetFormInputText(array('default' => 'Fabien')),
            'last_name' => new sfWidgetFormInputText(),
            'image' => new sfWidgetFormInputFile(),
        )));
        foreach ($form as $name => $value) {
            $values[$name] = $value;
        }
        $this->assertSame(true, isset($values['first_name']), '"sfForm" implements the Iterator interface');
        $this->assertSame(true, isset($values['last_name']), '"sfForm" implements the Iterator interface');
        $this->assertSame(array('first_name', 'last_name', 'image'), array_keys($values), '"sfForm" implements the Iterator interface');
    }

    public function testUseFields()
    {
        $form = new FormTest();
        $form->setWidgetSchema(new sfWidgetFormSchema(array(
            'first_name' => new sfWidgetFormInputText(),
            'last_name' => new sfWidgetFormInputText(),
            'email' => new sfWidgetFormInputText(),
        )));
        $form->useFields(array('first_name', 'last_name'));
        $this->assertSame(array('first_name', 'last_name'), $form->getWidgetSchema()->getPositions(), '->useFields() removes all fields except the ones given as an argument');

        $form->setWidgetSchema(new sfWidgetFormSchema(array(
            'first_name' => new sfWidgetFormInputText(),
            'last_name' => new sfWidgetFormInputText(),
            'email' => new sfWidgetFormInputText(),
        )));
        $form->useFields(array('email', 'first_name'));
        $this->assertSame(array('email', 'first_name'), $form->getWidgetSchema()->getPositions(), '->useFields() reorders the fields');

        $form->setWidgetSchema(new sfWidgetFormSchema(array(
            'first_name' => new sfWidgetFormInputText(),
            'last_name' => new sfWidgetFormInputText(),
            'email' => new sfWidgetFormInputText(),
        )));
        $form->useFields(array('email', 'first_name'), false);
        $this->assertSame(array('first_name', 'email'), $form->getWidgetSchema()->getPositions(), '->useFields() does not reorder the fields if the second argument is false');

        $form->setWidgetSchema(new sfWidgetFormSchema(array(
            'id' => new sfWidgetFormInputHidden(),
            'first_name' => new sfWidgetFormInputText(),
            'last_name' => new sfWidgetFormInputText(),
            'email' => new sfWidgetFormInputText(),
        )));
        $form->useFields(array('first_name', 'last_name'));
        $this->assertSame(array('first_name', 'last_name', 'id'), $form->getWidgetSchema()->getPositions(), '->useFields() does not remove hidden fields');
    }

    public function testBind()
    {
        $form = new FormTest();
        $form->setValidatorSchema(new sfValidatorSchema(array(
            'first_name' => new sfValidatorString(array('min_length' => 2)),
            'last_name' => new sfValidatorString(array('min_length' => 2)),
        )));
        $this->assertSame(true, !$form->isBound(), '->isBound() returns false if the form is not bound');
        $this->assertSame(array(), $form->getValues(), '->getValues() returns an empty array if the form is not bound');
        $this->assertSame(true, !$form->isValid(), '->isValid() returns false if the form is not bound');
        $this->assertSame(true, !$form->hasErrors(), '->hasErrors() returns false if the form is not bound');

        $this->assertSame(null, $form->getValue('first_name'), '->getValue() returns null if the form is not bound');
        $form->bind(array('first_name' => 'Fabien', 'last_name' => 'Potencier'));
        $this->assertSame(true, $form->isBound(), '->isBound() returns true if the form is bound');
        $this->assertSame(array('first_name' => 'Fabien', 'last_name' => 'Potencier'), $form->getValues(), '->getValues() returns an array of cleaned values if the form is bound');
        $this->assertSame(true, $form->isValid(), '->isValid() returns true if the form passes the validation');
        $this->assertSame(true, !$form->hasErrors(), '->hasErrors() returns false if the form passes the validation');
        $this->assertSame('Fabien', $form->getValue('first_name'), '->getValue() returns the cleaned value for a field name if the form is bound');
        $this->assertSame(null, $form->getValue('nonsense'), '->getValue() returns null when non-existant param is requested');

        $form->bind(array());
        $this->assertSame(true, !$form->isValid(), '->isValid() returns false if the form does not pass the validation');
        $this->assertSame(true, $form->hasErrors(), '->isValid() returns true if the form does not pass the validation');
        $this->assertSame(array(), $form->getValues(), '->getValues() returns an empty array if the form does not pass the validation');
        $this->assertSame('first_name [Required.] last_name [Required.]', $form->getErrorSchema()->getMessage(), '->getErrorSchema() returns an error schema object with all errors');

        $form = new FormTest();
        $form->setValidatorSchema(new sfValidatorSchema(array(
            1 => new sfValidatorString(array('min_length' => 2)),
            2 => new sfValidatorString(array('min_length' => 2)),
        )));
        $form->bind(array(1 => 'fabien', 2 => 'potencier'));
        $this->assertSame(true, $form->isValid(), '->bind() behaves correctly when field names are numeric');

        $form = new FormTest();
        $form->setValidatorSchema(new sfValidatorSchema(array(
            1 => new sfValidatorString(array('min_length' => 2)),
            2 => new sfValidatorString(array('min_length' => 2)),
            'file' => new sfValidatorFile(array('max_size' => 2)),
        )));
        $form->setWidgetSchema(new sfWidgetFormSchema(array('file' => new sfWidgetFormInputFile())));
        $form->bind(array(1 => 'f', 2 => 'potencier'), array(
            'file' => array(
                'error' => 0,
                'name' => 'test1.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/test1.txt',
                'size' => 100,
            ),
        ));
        $this->assertSame('1 [min_length] file [max_size]', $form->getErrorSchema()->getCode(), '->bind() behaves correctly with files');

        try {
            $form->bind(array(1 => 'f', 2 => 'potencier'));

            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }

        $pf = new FormTest(); // parent form
        $pf->setValidatorSchema(new sfValidatorSchema()); // cleaning sfValidatorSchema to silence `_token_`

        $ef = new FormTest(); // embed form

        $ef->setValidatorSchema(new sfValidatorSchema(array(
            1 => new sfValidatorString(array('min_length' => 2)),
            2 => new sfValidatorString(array('min_length' => 2)),
            'file' => new sfValidatorFile(array('max_size' => 2)),
        )));
        $ef->setWidgetSchema(new sfWidgetFormSchema(array('file' => new sfWidgetFormInputFile())));
        $pf->embedForm('ef', $ef);
        $pf->bind(array('ef' => array(1 => 'f', 2 => 'potencier')), array('ef' => array(
            'file' => array(
                'error' => 0,
                'name' => 'test1.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/test1.txt',
                'size' => 100,
            ),
        )));
        $this->assertSame('ef [1 [min_length] file [max_size]]', $pf->getErrorSchema()->getCode(), '->bind() behaves correctly with files in embed form');
    }

    public function testRenderGlobalErrors()
    {
        $form = new FormTest();
        $form->setValidatorSchema(new sfValidatorSchema(array(
            'id' => new sfValidatorInteger(),
            'first_name' => new sfValidatorString(array('min_length' => 2)),
            'last_name' => new sfValidatorString(array('min_length' => 2)),
        )));
        $form->setWidgetSchema(new sfWidgetFormSchema(array(
            'id' => new sfWidgetFormInputHidden(),
            'first_name' => new sfWidgetFormInputText(),
            'last_name' => new sfWidgetFormInputText(),
        )));
        $form->bind(array(
            'id' => 'dddd',
            'first_name' => 'f',
            'last_name' => 'potencier',
        ));
        $output = <<<'EOF'
  <ul class="error_list">
    <li>Id: "dddd" is not an integer.</li>
  </ul>

EOF;
        $this->assertSame(fix_linebreaks($output), $form->renderGlobalErrors(), '->renderGlobalErrors() renders global errors as an HTML list');
    }

    public function testRender()
    {
        $form = new FormTest(array('first_name' => 'Fabien', 'last_name' => 'Potencier'));
        $form->setValidators(array(
            'id' => new sfValidatorInteger(),
            'first_name' => new sfValidatorString(array('min_length' => 2)),
            'last_name' => new sfValidatorString(array('min_length' => 2)),
        ));
        $form->setWidgets(array(
            'id' => new sfWidgetFormInputHidden(array('default' => 3)),
            'first_name' => new sfWidgetFormInputText(array('default' => 'Thomas')),
            'last_name' => new sfWidgetFormInputText(),
        ));

        // unbound
        $output = <<<'EOF'
<tr>
  <th><label for="first_name">First name</label></th>
  <td><input type="text" name="first_name" value="Fabien" id="first_name" /></td>
</tr>
<tr>
  <th><label for="last_name">Last name</label></th>
  <td><input type="text" name="last_name" value="Potencier" id="last_name" /><input type="hidden" name="id" value="3" id="id" /></td>
</tr>

EOF;
        $this->assertSame(fix_linebreaks($output), $form->__toString(), '->__toString() renders the form as HTML');
        $output = <<<'EOF'
<tr>
  <th><label for="first_name">First name</label></th>
  <td><input type="text" name="first_name" value="Fabien" class="foo" id="first_name" /></td>
</tr>
<tr>
  <th><label for="last_name">Last name</label></th>
  <td><input type="text" name="last_name" value="Potencier" id="last_name" /><input type="hidden" name="id" value="3" id="id" /></td>
</tr>

EOF;
        $this->assertSame(fix_linebreaks($output), $form->render(array('first_name' => array('class' => 'foo'))), '->render() renders the form as HTML');
        $this->assertSame('<input type="hidden" name="id" value="3" id="id" />', (string) $form['id'], '->offsetGet() returns a sfFormField');
        $this->assertSame('<input type="text" name="first_name" value="Fabien" id="first_name" />', (string) $form['first_name'], '->offsetGet() returns a sfFormField');
        $this->assertSame('<input type="text" name="last_name" value="Potencier" id="last_name" />', (string) $form['last_name'], '->offsetGet() returns a sfFormField');

        // bound
        $form->bind(array(
            'id' => '1',
            'first_name' => 'Fabien',
            'last_name' => 'Potencier',
        ));
        $output = <<<'EOF'
<tr>
  <th><label for="first_name">First name</label></th>
  <td><input type="text" name="first_name" value="Fabien" id="first_name" /></td>
</tr>
<tr>
  <th><label for="last_name">Last name</label></th>
  <td><input type="text" name="last_name" value="Potencier" id="last_name" /><input type="hidden" name="id" value="1" id="id" /></td>
</tr>

EOF;
        $this->assertSame(fix_linebreaks($output), $form->__toString(), '->__toString() renders the form as HTML');
        $output = <<<'EOF'
<tr>
  <th><label for="first_name">First name</label></th>
  <td><input type="text" name="first_name" value="Fabien" class="foo" id="first_name" /></td>
</tr>
<tr>
  <th><label for="last_name">Last name</label></th>
  <td><input type="text" name="last_name" value="Potencier" id="last_name" /><input type="hidden" name="id" value="1" id="id" /></td>
</tr>

EOF;
        $this->assertSame(fix_linebreaks($output), $form->render(array('first_name' => array('class' => 'foo'))), '->render() renders the form as HTML');
        $this->assertSame('<input type="hidden" name="id" value="1" id="id" />', (string) $form['id'], '->offsetGet() returns a sfFormField');
        $this->assertSame('<input type="text" name="first_name" value="Fabien" id="first_name" />', (string) $form['first_name'], '->offsetGet() returns a sfFormField');
        $this->assertSame('<input type="text" name="last_name" value="Potencier" id="last_name" />', (string) $form['last_name'], '->offsetGet() returns a sfFormField');
    }

    public function testRenderUsing()
    {
        $form = new sfForm();
        $form->setWidgets(array('name' => new sfWidgetFormInputText()));
        $output = <<<'EOF'
<li>
  <label for="name">Name</label>
  <input type="text" name="name" id="name" />
</li>

EOF;
        $this->assertSame(fix_linebreaks($output), $form->renderUsing('list'), 'renderUsing() renders the widget schema using the given form formatter');
        $this->assertSame('table', $form->getWidgetSchema()->getFormFormatterName(), 'renderUsing() does not persist form formatter name for the current form instance');

        $w = $form->getWidgetSchema();
        $w->addFormFormatter('custom', new sfWidgetFormSchemaFormatterList($w));
        $this->assertSame(fix_linebreaks($output), $form->renderUsing('custom'), 'renderUsing() renders a custom form formatter');

        $this->expectException(InvalidArgumentException::class);
        $form->renderUsing('nonexistant');
    }

    public function testHiddenFields()
    {
        $form = new sfForm();
        $form->setWidgets(array(
            'id' => new sfWidgetFormInputHidden(),
            'name' => new sfWidgetFormInputText(),
            'is_admin' => new sfWidgetFormInputHidden(),
        ));
        $output = '<input type="hidden" name="id" id="id" /><input type="hidden" name="is_admin" id="is_admin" />';
        $this->assertSame($output, $form->renderHiddenFields(), 'renderHiddenFields() renders all hidden fields, no visible fields');
        $this->assertSame(3, count($form->getFormFieldSchema()), 'renderHiddenFields() does not modify the form fields');

        $author = new sfForm();
        $author->setWidgets(array('id' => new sfWidgetFormInputHidden(), 'name' => new sfWidgetFormInputText()));

        $company = new sfForm();
        $company->setWidgets(array('id' => new sfWidgetFormInputHidden(), 'name' => new sfWidgetFormInputText()));

        $author->embedForm('company', $company);

        $output = '<input type="hidden" name="id" id="id" /><input type="hidden" name="company[id]" id="company_id" />';
        $this->assertSame($output, $author->renderHiddenFields(), 'renderHiddenFields() renders hidden fields from embedded forms');

        $output = '<input type="hidden" name="id" id="id" />';
        $this->assertSame($output, $author->renderHiddenFields(false), 'renderHiddenFields() does not render hidden fields from embedded forms if the first parameter is "false"');
    }

    public function testEmbedForm()
    {
        $author = new FormTest(array('first_name' => 'Fabien'));
        $author->setWidgetSchema($author_widget_schema = new sfWidgetFormSchema(array('first_name' => new sfWidgetFormInputText())));
        $author->setValidatorSchema($author_validator_schema = new sfValidatorSchema(array('first_name' => new sfValidatorString(array('min_length' => 2)))));
        $author->addCSRFProtection(null);

        $company = new FormTest();
        $company->setWidgetSchema($company_widget_schema = new sfWidgetFormSchema(array('name' => new sfWidgetFormInputText())));
        $company->setValidatorSchema($company_validator_schema = new sfValidatorSchema(array('name' => new sfValidatorString(array('min_length' => 2)))));
        $company->addCSRFProtection(null);

        $article = new FormTest();
        $article->setWidgetSchema($article_widget_schema = new sfWidgetFormSchema(array('title' => new sfWidgetFormInputText())));
        $article->setValidatorSchema($article_validator_schema = new sfValidatorSchema(array('title' => new sfValidatorString(array('min_length' => 2)))));
        $article->addCSRFProtection(null);

        $author->embedForm('company', $company);
        $article->embedForm('author', $author);
        $v = $article->getValidatorSchema();
        $w = $article->getWidgetSchema();
        $d = $article->getDefaults();
        $form = $article->getEmbeddedForms();

        $w->setNameFormat('article[%s]');

        $this->assertInstanceOf(sfValidatorPass::class, $v['author'], '->embedForm() set validator pass');
        // ignore parents in comparison
        $w['author']['first_name']->setParent(null);
        $author_widget_schema['first_name']->setParent(null);
        $this->assertSame(true, $w['author']['first_name'] == $author_widget_schema['first_name'], '->embedForm() embeds the widget schema');
        $this->assertSame('Fabien', $d['author']['first_name'], '->embedForm() merges default values from the embedded form');
        $this->assertSame(null, $w['author'][sfForm::getCSRFFieldName()], '->embedForm() removes the CSRF token for the embedded form');
        $this->assertSame(false, isset($form['author'][sfForm::getCSRFFieldName()]), '->embedForm() removes the CSRF token for the embedded form');

        $this->assertSame(
            'article[author][first_name]',
            $w['author']->generateName('first_name'),
            '->embedForm() changes the name format to reflect the embedding'
        );
        $this->assertSame(
            'article[author][company][name]',
            $w['author']['company']->generateName('name'),
            '->embedForm() changes the name format to reflect the embedding'
        );

        // tests for ticket #56
        $this->assertSame(
            true,
            $author->getValidator('company') == $company_validator_schema,
            '->getValidator() gets a validator schema for an embedded form'
        );
        try {
            $author->setValidator('company', new sfValidatorPass());

            $this->fail('"sfForm" Trying to set a validator for an embedded form field throws a LogicException');
        } catch (LogicException $e) {
            $this->assertInstanceOf(
                LogicException::class,
                $e,
                '"sfForm" Trying to set a validator for an embedded form field throws a LogicException'
            );
        }

        // tests for ticket #4754
        $f1 = new TestForm1();
        $f2 = new TestForm2();
        $f1->embedForm('f2', $f2);
        $this->assertSame(
            '<textarea rows="4" cols="30" name="f2[c]" id="f2_c"></textarea>',
            $f1['f2']['c']->render(),
            '->embedForm() generates a correct id in embedded form fields'
        );
        $this->assertSame(
            '<label for="f2_c">2_c</label>',
            $f1['f2']['c']->renderLabel(),
            '->embedForm() generates a correct label id correctly in embedded form fields'
        );

        // bind too many values for embedded forms
        $list = new FormTest();
        $list->setWidgets(array('title' => new sfWidgetFormInputText()));
        $list->setValidators(array('title' => new sfValidatorString()));
        $containerForm = new sfForm();
        $containerForm->embedForm('0', clone $list);
        $containerForm->embedForm('1', clone $list);
        $list->embedForm('items', $containerForm);
        $list->bind(array(
            'title' => 'list title',
            'items' => array(
                array('title' => 'item 1'),
                array('title' => 'item 2'),
                array('title' => 'extra item'),
            ),
        ));

        $this->assertInstanceOf(
            sfValidatorErrorSchema::class,
            $list['items'][0]->getError(),
            '"sfFormFieldSchema" is given an error schema when an extra embedded form is bound'
        );

        // does this trigger a fatal error?
        $list['items']->render();
        $this->assertSame(true, true, '"sfFormFieldSchema" renders when an extra embedded form is bound');
    }

    public function testEmbeddedForms()
    {
        $article = new FormTest();
        $company = new FormTest();
        $author = new FormTest();
        $article->embedForm('company', $company);
        $article->embedForm('author', $author);
        $forms = $article->getEmbeddedForms();
        $this->assertSame(array('company', 'author'), array_keys($forms), '->getEmbeddedForms() returns the embedded forms');
        $this->assertSame($company, $forms['company'], '->getEmbeddedForms() returns the embedded forms');
        $this->assertInstanceOf(FormTest::class, $article->getEmbeddedForm('company'), '->getEmbeddedForm() return an embedded form');

        try {
            $article->getEmbeddedForm('nonexistant');
            $this->fail('->getEmbeddedForm() throws an exception if the embedded form does not exist');
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e, '->getEmbeddedForm() throws an exception if the embedded form does not exist');
        }
    }

    public function testConvertFileInformation()
    {
        $input = array(
            'file' => array(
                'error' => 0,
                'name' => 'test1.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/test1.txt',
                'size' => 100,
            ),
            'file1' => array(
                'error' => 0,
                'name' => 'test2.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/test1.txt',
                'size' => 200,
            ),
        );
        $this->assertSame($input, sfForm::convertFileInformation($input), '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');

        $input = array(
            'article' => array(
                'name' => array(
                    'file1' => 'test1.txt',
                    'file2' => 'test2.txt',
                ),
                'type' => array(
                    'file1' => 'text/plain',
                    'file2' => 'text/plain',
                ),
                'tmp_name' => array(
                    'file1' => '/tmp/test1.txt',
                    'file2' => '/tmp/test2.txt',
                ),
                'error' => array(
                    'file1' => 0,
                    'file2' => 0,
                ),
                'size' => array(
                    'file1' => 100,
                    'file2' => 200,
                ),
            ),
        );
        $expected = array(
            'article' => array(
                'file1' => array(
                    'error' => 0,
                    'name' => 'test1.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/test1.txt',
                    'size' => 100,
                ),
                'file2' => array(
                    'error' => 0,
                    'name' => 'test2.txt',
                    'type' => 'text/plain',
                    'tmp_name' => '/tmp/test2.txt',
                    'size' => 200,
                ),
            ),
        );
        $this->assertSame($expected, sfForm::convertFileInformation($input), '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');
        $this->assertSame($expected, sfForm::convertFileInformation($expected), '::convertFileInformation() only changes the input array if needed');

        $input = array(
            'file' => array(
                'error' => 0,
                'name' => 'test.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/test.txt',
                'size' => 100,
            ),
            'article' => array(
                'name' => array(
                    'name' => array(
                        'name' => 'test1.txt',
                        'another' => array('file2' => 'test2.txt'),
                    ),
                ),
                'type' => array(
                    'name' => array(
                        'name' => 'text/plain',
                        'another' => array('file2' => 'text/plain'),
                    ),
                ),
                'tmp_name' => array(
                    'name' => array(
                        'name' => '/tmp/test1.txt',
                        'another' => array('file2' => '/tmp/test2.txt'),
                    ),
                ),
                'error' => array(
                    'name' => array(
                        'name' => 0,
                        'another' => array('file2' => 0),
                    ),
                ),
                'size' => array(
                    'name' => array(
                        'name' => 100,
                        'another' => array('file2' => 200),
                    ),
                ),
            ),
        );
        $expected = array(
            'file' => array(
                'error' => 0,
                'name' => 'test.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/test.txt',
                'size' => 100,
            ),
            'article' => array(
                'name' => array(
                    'name' => array(
                        'error' => 0,
                        'name' => 'test1.txt',
                        'type' => 'text/plain',
                        'tmp_name' => '/tmp/test1.txt',
                        'size' => 100,
                    ),
                    'another' => array(
                        'file2' => array(
                            'error' => 0,
                            'name' => 'test2.txt',
                            'type' => 'text/plain',
                            'tmp_name' => '/tmp/test2.txt',
                            'size' => 200,
                        ),
                    ),
                ),
            ),
        );
        $this->assertSame($expected, sfForm::convertFileInformation($input), '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');
        $this->assertSame($expected, sfForm::convertFileInformation($expected), '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');
    }

    public function testRenderFormTag()
    {
        $form = new FormTest();
        $this->assertSame('<form action="/url" method="post">', $form->renderFormTag('/url'), '->renderFormTag() renders the form tag');
        $this->assertSame('<form method="post" action="/url"><input type="hidden" name="sf_method" value="put" />', $form->renderFormTag('/url', array('method' => 'put')), '->renderFormTag() adds a hidden input tag if the method is not GET or POST');

        $form->setWidgetSchema(new sfWidgetFormSchema(array('image' => new sfWidgetFormInputFile())));
        $this->assertSame('<form action="/url" method="post" enctype="multipart/form-data">', $form->renderFormTag('/url'), '->renderFormTag() adds the enctype attribute if the form is multipart');
    }

    public function testClone()
    {
        $a = new FormTest();
        $a->setValidatorSchema(new sfValidatorSchema(array(
            'first_name' => new sfValidatorString(array('min_length' => 2)),
        )));
        $a->bind(array('first_name' => 'F'));
        $a1 = clone $a;

        $this->assertSame(true, $a1->getValidatorSchema() !== $a->getValidatorSchema(), '__clone() clones the validator schema');
        $this->assertSame(true, $a1->getValidatorSchema() == $a->getValidatorSchema(), '__clone() clones the validator schema');

        $this->assertSame(true, $a1->getWidgetSchema() !== $a->getWidgetSchema(), '__clone() clones the widget schema');
        $this->assertSame(true, $a1->getWidgetSchema() == $a->getWidgetSchema(), '__clone() clones the widget schema');

        $this->assertSame(true, $a1->getErrorSchema() !== $a->getErrorSchema(), '__clone() clones the error schema');
        $this->assertSame(true, $a1->getErrorSchema()->getMessage() == $a->getErrorSchema()->getMessage(), '__clone() clones the error schema');
    }

    public function testMergeForm()
    {
        $f1 = new TestForm1();
        $f2 = new TestForm2();
        $f1->mergeForm($f2);

        $widgetSchema = $f1->getWidgetSchema();
        $validatorSchema = $f1->getValidatorSchema();
        $this->assertSame(4, count($widgetSchema->getFields()), 'mergeForm() merges a widget form schema');
        $this->assertSame(4, count($validatorSchema->getFields()), 'mergeForm() merges a validator schema');
        $this->assertSame(array('a', 'b', 'c', 'd'), array_keys($widgetSchema->getFields()), 'mergeForms() merges the correct widgets');
        $this->assertSame(array('a', 'b', 'c', 'd'), array_keys($validatorSchema->getFields()), 'mergeForms() merges the correct validators');

        $expectedLabels = array(
            'a' => '1_a',
            'b' => '1_b',
            'c' => '2_c',
            'd' => '2_d',
        );
        $this->assertSame($expectedLabels, $widgetSchema->getLabels(), 'mergeForm() merges labels correctly');

        $expectedHelps = array(
            'a' => '1_a',
            'b' => '1_b',
            'c' => '2_c',
            'd' => '2_d',
        );
        $this->assertSame($expectedHelps, $widgetSchema->getHelps(), 'mergeForm() merges helps correctly');
        $this->assertInstanceOf(sfWidgetFormTextarea::class, $widgetSchema['c'], 'mergeForm() overrides original form widget');
        $this->assertInstanceOf(sfValidatorPass::class, $validatorSchema['c'], 'mergeForm() overrides original form validator');
        $this->assertInstanceOf(sfValidatorPass::class, $validatorSchema->getPreValidator(), 'mergeForm() merges pre validator');
        $this->assertInstanceOf(sfValidatorPass::class, $validatorSchema->getPostValidator(), 'mergeForm() merges post validator');

        try {
            $f1->bind(array('a' => 'foo', 'b' => 'bar', 'd' => 'far_too_long_value'));
            $f1->mergeForm($f2);
            $this->fail('mergeForm() disallows merging already bound forms');
        } catch (LogicException $e) {
            $this->assertInstanceOf(LogicException::class, $e, 'mergeForm() disallows merging already bound forms');
        }

        $errorSchema = $f1->getErrorSchema();
        $this->assertSame(true, array_key_exists('d', $errorSchema->getErrors()), 'mergeForm() merges errors after having been bound');

        $f1 = new TestForm1();
        $f1->getWidgetSchema()->moveField('a', 'last');

        // is moved field well positioned when accessed with iterator interface? (#5551)
        foreach ($f1 as $f1name => $f1field) {
            $this->assertSame('b', $f1name, 'iterating on form takes in account ->moveField() operations.');
            break;
        }

        $f2 = new TestForm2();
        $f2->mergeForm($f1);

        $this->assertSame(array('c', 'd', 'b', 'a'), array_keys($f2->getWidgetSchema()->getFields()), 'mergeForm() merges fields in the correct order');

        $f1 = new NumericFieldsForm(array('5' => 'default1'), array('salt' => '1'));
        $f2 = new NumericFieldsForm(array('5' => 'default2'), array('salt' => '2'));
        $f1->mergeForm($f2);

        $this->assertSame(array('5' => 'default2'), $f1->getDefaults(), '->mergeForm() merges numeric defaults');
        $this->assertSame(array('5' => 'label2'), $f1->getWidgetSchema()->getLabels(), '->mergeForm() merges numeric labels');
        $this->assertSame(array('5' => 'help2'), $f1->getWidgetSchema()->getHelps(), '->mergeForm() merges numeric helps');
    }

    public function testJavaScriptsAndStylesheets()
    {
        $form = new FormTest();
        $form->setWidgets(array(
            'foo' => new MyWidget(array('name' => 'foo')),
            'bar' => new MyWidget(array('name' => 'bar')),
        ));
        $this->assertSame(array('/path/to/a/foo.js', '/path/to/a/bar.js'),
            $form->getJavaScripts(),
            '->getJavaScripts() returns the stylesheets of all widgets');

        $this->assertSame(array('/path/to/a/foo.css' => 'all', '/path/to/a/bar.css' => 'all'),
            $form->getStylesheets(),
            '->getStylesheets() returns the JavaScripts of all widgets');
    }

    public function testFormFieldSchema()
    {
        $form = new NumericFieldsForm(array('5' => 'default'));
        $this->assertSame(array('5' => 'default'), $form->getFormFieldSchema()->getValue(), '->getFormFieldSchema() includes default numeric fields');

        $form->bind(array('5' => 'bound'));
        $this->assertSame(array('5' => 'bound'), $form->getFormFieldSchema()->getValue(), '->getFormFieldSchema() includes bound numeric fields');
    }

    public function testErrors()
    {
        $f1 = new TestForm1();
        $f21 = new TestForm1();
        $f2 = new TestForm2();
        $f2->embedForm('F21', $f21);
        $f1->embedForm('F2', $f2);
        $f1->bind(array());
        $expected = array(
            '1_a' => 'Required.',
            '1_b' => 'Required.',
            '1_c' => 'Required.',
            'F2' => array(
                '2_d' => 'Required.',
                'F21' => array(
                    '1_a' => 'Required.',
                    '1_b' => 'Required.',
                    '1_c' => 'Required.',
                ),
            ),
        );

        $this->assertSame($expected, $f1->getErrors(), '->getErrors() return array of errors');

        // bind with a simulated file upload in the POST array
        $form = new FormTest();
        try {
            $form->bind(array(
                'file' => array(
                    'name' => 'foo.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'somefile',
                    'error' => 0,
                    'size' => 10,
                ),
            ));
            $this->fail('Cannot fake a file upload with a POST');
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e, 'Cannot fake a file upload with a POST');
        }

        $form = new FormTest();
        try {
            $form->bind(array(
                'foo' => array(
                    'bar' => array(
                        'file' => array(
                            'name' => 'foo.txt',
                            'type' => 'text/plain',
                            'tmp_name' => 'somefile',
                            'error' => 0,
                            'size' => 10,
                        ),
                    ),
                ),
            ));
            $this->fail('Cannot fake a file upload with a POST');
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e, 'Cannot fake a file upload with a POST');
        }
    }
}
