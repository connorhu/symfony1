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
class sfValidatorI18nChoiceLanguageTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ->configure()
        $this->diag('->configure()');

        try {
            new sfValidatorI18nChoiceLanguage(array('languages' => array('xx')));
            $this->fail('->configure() throws an InvalidArgumentException if a language does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->configure() throws an InvalidArgumentException if a language does not exist');
        }

        $v = new sfValidatorI18nChoiceLanguage(array('languages' => array('fr', 'en')));
        $this->is($v->getOption('choices'), array('fr', 'en'), '->configure() can restrict the number of languages with the languages option');

        // ->clean()
        $this->diag('->clean()');
        $v = new sfValidatorI18nChoiceLanguage(array('languages' => array('fr', 'en')));
        $this->is($v->clean('fr'), 'fr', '->clean() cleans the input value');
    }
}
