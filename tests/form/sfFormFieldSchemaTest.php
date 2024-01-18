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
class sfFormFieldSchemaTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // widgets
        $authorSchema = new sfWidgetFormSchema(array(
            'name' => $nameWidget = new sfWidgetFormInputText(),
        ));
        $authorSchema->setNameFormat('article[author][%s]');

        $schema = new sfWidgetFormSchema(array(
            'title' => $titleWidget = new sfWidgetFormInputText(),
            'author' => $authorSchema,
        ));
        $schema->setNameFormat('article[%s]');

        // errors
        $authorErrorSchema = new sfValidatorErrorSchema(new sfValidatorString());
        $authorErrorSchema->addError(new sfValidatorError(new sfValidatorString(), 'name error'), 'name');

        $articleErrorSchema = new sfValidatorErrorSchema(new sfValidatorString());
        $articleErrorSchema->addError($titleError = new sfValidatorError(new sfValidatorString(), 'title error'), 'title');
        $articleErrorSchema->addError($authorErrorSchema, 'author');

        $parent = new sfFormFieldSchema($schema, null, 'article', array('title' => 'symfony', 'author' => array('name' => 'Fabien')), $articleErrorSchema);
        $f = $parent['title'];
        $child = $parent['author'];

        // ArrayAccess interface
        $this->diag('ArrayAccess interface');
        $this->is(isset($parent['title']), true, 'sfFormField implements the ArrayAccess interface');
        $this->is(isset($parent['title1']), false, 'sfFormField implements the ArrayAccess interface');
        $this->is($parent['title'], $f, 'sfFormField implements the ArrayAccess interface');
        try {
            unset($parent['title']);
            $this->fail('sfFormField implements the ArrayAccess interface but in read-only mode');
        } catch (LogicException $e) {
            $this->pass('sfFormField implements the ArrayAccess interface but in read-only mode');
        }

        try {
            $parent['title'] = null;
            $this->fail('sfFormField implements the ArrayAccess interface but in read-only mode');
        } catch (LogicException $e) {
            $this->pass('sfFormField implements the ArrayAccess interface but in read-only mode');
        }

        try {
            $parent['title1'];
            $this->fail('sfFormField implements the ArrayAccess interface but in read-only mode');
        } catch (LogicException $e) {
            $this->pass('sfFormField implements the ArrayAccess interface but in read-only mode');
        }

        // implements Countable
        $this->diag('implements Countable');
        $widgetSchema = new sfWidgetFormSchema(array(
            'w1' => $w1 = new sfWidgetFormInputText(),
            'w2' => $w2 = new sfWidgetFormInputText(),
        ));
        $f = new sfFormFieldSchema($widgetSchema, null, 'article', array());
        $this->is(count($f), 2, 'sfFormFieldSchema implements the Countable interface');

        // implements Iterator
        $this->diag('implements Iterator');
        $f = new sfFormFieldSchema($widgetSchema, null, 'article', array());

        $values = array();
        foreach ($f as $name => $value) {
            $values[$name] = $value;
        }
        $this->is(isset($values['w1']), true, 'sfFormFieldSchema implements the Iterator interface');
        $this->is(isset($values['w2']), true, 'sfFormFieldSchema implements the Iterator interface');
        $this->is(count($values), 2, 'sfFormFieldSchema implements the Iterator interface');

        $this->diag('implements Iterator respecting the order of fields');
        $widgetSchema->moveField('w2', 'first');
        $f = new sfFormFieldSchema($widgetSchema, null, 'article', array());

        $values = array();
        foreach ($f as $name => $value) {
            $values[$name] = $value;
        }
        $this->is(array_keys($values), array('w2', 'w1'), 'sfFormFieldSchema keeps the order');
    }
}
