<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../PhpUnitSfTestHelperTrait.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWidgetFormI18nDateTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->configure()
        $this->diag('->configure()');

        $w = new sfWidgetFormI18nDate(array('culture' => 'fr'));
        $this->is($w->getOption('format'), '%day%/%month%/%year%', '->configure() automatically changes the date format for the given culture');
        $w = new sfWidgetFormI18nDate(array('culture' => 'en_US'));
        $this->is($w->getOption('format'), '%month%/%day%/%year%', '->configure() automatically changes the date format for the given culture');
        $w = new sfWidgetFormI18nDate(array('culture' => 'sr'));
        $this->is($w->getOption('format'), '%day%.%month%.%year%.', '->configure() automatically changes the date format for the given culture');

        $w = new sfWidgetFormI18nDate(array('culture' => 'fr', 'month_format' => 'name'));
        $months = $w->getOption('months');
        $this->is($months[2], 'février', '->configure() automatically changes the date format for the given culture');

        $w = new sfWidgetFormI18nDate(array('culture' => 'fr', 'month_format' => 'short_name'));
        $months = $w->getOption('months');
        $this->is($months[2], 'févr.', '->configure() automatically changes the date format for the given culture');

        $w = new sfWidgetFormI18nDate(array('culture' => 'fr', 'month_format' => 'number'));
        $months = $w->getOption('months');
        $this->is($months[2], '02', '->configure() automatically changes the date format for the given culture');

        try {
            new sfWidgetFormI18nDate(array('culture' => 'fr', 'month_format' => 'nonexistant'));
            $this->fail('->configure() throws an InvalidArgumentException if the month_format type does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->configure() throws an InvalidArgumentException if the month_format type does not exist');
        }
    }
}
