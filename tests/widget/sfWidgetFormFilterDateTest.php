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
require_once __DIR__.'/../fixtures/WidgetFormStub.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetFormFilterDateTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->render()
        $this->diag('->render()');

        $ws = new sfWidgetFormSchema();
        $ws->addFormFormatter('stub', new FormFormatterStub());
        $ws->setFormFormatterName('stub');
        $w = new sfWidgetFormFilterDate(array('from_date' => new WidgetFormStub(), 'to_date' => new WidgetFormStub()));
        $w->setParent($ws);
        $dom->loadHTML($w->render('foo'));
        $css = new sfDomCssSelector($dom);
        $this->is($css->matchSingle('label[for="foo_is_empty"]')->getValue(), 'translation[is empty]', '->render() translates the empty_label option');
    }
}
