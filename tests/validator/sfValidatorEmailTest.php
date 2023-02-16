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
class sfValidatorEmailTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v = new sfValidatorEmail();

        // ->clean()
        $this->diag('->clean()');
        foreach (array(
            'fabien.potencier@symfony-project.com',
            'example@example.co.uk',
            'fabien_potencier@example.fr',
        ) as $url) {
            $this->is($v->clean($url), $url, '->clean() checks that the value is a valid email');
        }

        foreach (array(
            'example',
            'example@',
            'example@localhost',
            'example@example.com@example.com',
        ) as $nonUrl) {
            try {
                $v->clean($nonUrl);
                $this->fail('->clean() throws an sfValidatorError if the value is not a valid email');
                $this->skip('', 1);
            } catch (sfValidatorError $e) {
                $this->pass('->clean() throws an sfValidatorError if the value is not a valid email');
                $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
            }
        }
    }
}
