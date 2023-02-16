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
class sfValidatorI18nChoiceTimezoneTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->configure()
        $this->diag('->configure()');

        // ->clean()
        $this->diag('->clean()');
        $v = new sfValidatorI18nChoiceTimezone();
        $this->is($v->clean('Europe/Paris'), 'Europe/Paris', '->clean() cleans the input value');
    }
}
