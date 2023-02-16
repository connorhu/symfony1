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
class sfWidgetFormI18nTimeTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;

        // ->configure()
        $this->diag('->configure()');

        $w = new sfWidgetFormI18nTime(array('culture' => 'fr'));
        $this->is($w->getOption('format'), '%hour%:%minute%:%second%', '->configure() automatically changes the date format for the given culture');
        $this->is($w->getOption('format_without_seconds'), '%hour%:%minute%', '->configure() automatically changes the date format for the given culture');

        $w = new sfWidgetFormI18nTime(array('culture' => 'sr'));
        $this->is($w->getOption('format'), '%hour%.%minute%.%second%', '->configure() automatically changes the date format for the given culture');
        $this->is($w->getOption('format_without_seconds'), '%hour%.%minute%', '->configure() automatically changes the date format for the given culture');
    }
}
