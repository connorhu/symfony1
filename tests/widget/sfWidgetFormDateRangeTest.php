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
require_once __DIR__.'/../fixtures/FormFormatterMock.php';
require_once __DIR__.'/../fixtures/WidgetFormStub.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetFormDateRangeTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->render()
        $this->diag('->render()');

        $ws = new sfWidgetFormSchema();
        $ws->addFormFormatter('stub', $formatter = new FormFormatterMock());
        $ws->setFormFormatterName('stub');
        $w = new sfWidgetFormDateRange(array('from_date' => new WidgetFormStub(), 'to_date' => new WidgetFormStub()));
        $w->setParent($ws);
        $this->is($w->render('foo'), 'translation[from ##WidgetFormStub## to ##WidgetFormStub##]', '->render() remplaces %from_date% and %to_date%');
        $this->is($formatter->translateSubjects, array('from %from_date% to %to_date%'), '->render() translates the template option');
    }
}
