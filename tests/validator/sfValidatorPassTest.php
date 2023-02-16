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
class sfValidatorPassTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v = new sfValidatorPass();

        // ->clean()
        $this->diag('->clean()');
        $this->is($v->clean(''), '', '->clean() always returns the value unmodified');
        $this->is($v->clean(null), null, '->clean() always returns the value unmodified');
    }
}
