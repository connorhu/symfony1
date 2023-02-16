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
class sfValidatorI18nChoiceCountryTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->configure()
        $this->diag('->configure()');

        try {
            new sfValidatorI18nChoiceCountry(array('countries' => array('EN')));
            $this->fail('->configure() throws an InvalidArgumentException if a country does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->configure() throws an InvalidArgumentException if a country does not exist');
        }

        $v = new sfValidatorI18nChoiceCountry(array('countries' => array('FR', 'GB')));
        $this->is($v->getOption('choices'), array('FR', 'GB'), '->configure() can restrict the number of countries with the countries option');

        // ->clean()
        $this->diag('->clean()');
        $v = new sfValidatorI18nChoiceCountry(array('countries' => array('FR', 'GB')));
        $this->is($v->clean('FR'), 'FR', '->clean() cleans the input value');
    }
}
