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
class sfWidgetFormSchemaDecoratorTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $w1 = new sfWidgetFormInputText();
        $w2 = new sfWidgetFormInputText();
        $ws = new sfWidgetFormSchema(array('w1' => $w1));

        $w = new sfWidgetFormSchemaDecorator($ws, "<table>\n%content%</table>");

        // ->getWidget()
        $this->diag('->getWidget()');
        $this->is($w->getWidget(), $ws, '->getWidget() returns the decorated widget');

        // ->render()
        $this->diag('->render()');
        $output = <<<'EOF'
        <table>
        <tr>
          <th><label for="w1">W1</label></th>
          <td><input type="text" name="w1" id="w1" /></td>
        </tr>
        </table>
        EOF;
        $this->is($w->render(null), fix_linebreaks($output), '->render() decorates the widget');

        // implements ArrayAccess
        $this->diag('implements ArrayAccess');
        $w['w2'] = $w2;
        $w1->setParent($ws);
        $w2->setParent($ws);
        $this->ok($w->getFields() == array('w1' => $w1, 'w2' => $w2), 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');
        $this->ok($ws->getFields() == array('w1' => $w1, 'w2' => $w2), 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');

        try {
            $w['w1'] = 'string';
            $this->fail('sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');
        } catch (LogicException $e) {
            $this->pass('sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');
        }

        $w = new sfWidgetFormSchemaDecorator($ws, "<table>\n%content%</table>");
        $this->is(isset($w['w1']), true, 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');
        $this->is(isset($w['w2']), true, 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');
        $this->is(isset($ws['w1']), true, 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');
        $this->is(isset($ws['w2']), true, 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');

        $w = new sfWidgetFormSchemaDecorator($ws, "<table>\n%content%</table>");
        $this->ok($w['w1'] == $w1, 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');
        $this->ok($w['w2'] == $w2, 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');
        $this->ok($ws['w1'] == $w1, 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');
        $this->ok($ws['w2'] == $w2, 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');

        $w = new sfWidgetFormSchemaDecorator($ws, "<table>\n%content%</table>");
        unset($w['w1']);
        $this->is($w['w1'], null, 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');
        $this->is($ws['w1'], null, 'sfWidgetFormSchemaDecorator implements the ArrayAccess interface for the fields');

        // __clone()
        $this->diag('__clone()');
        $w1 = clone $w;
        $this->ok($w1->getWidget() !== $w->getWidget(), '__clone() clones the embedded widget');
        // $this->ok($w1->getWidget() == $w->getWidget(), '__clone() clones the embedded widget');
    }
}
