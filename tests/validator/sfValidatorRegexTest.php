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
class sfValidatorRegexTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        function generate_regex()
        {
            return '/^123$/';
        }

        // __construct()
        $this->diag('__construct()');
        try {
            new sfValidatorRegex();
            $this->fail('__construct() throws an RuntimeException if you don\'t pass a pattern option');
        } catch (RuntimeException $e) {
            $this->pass('__construct() throws an RuntimeException if you don\'t pass a pattern option');
        }

        // ->clean()
        $this->diag('->clean()');

        $v = new sfValidatorRegex(array('pattern' => '/^[0-9]+$/'));
        $this->is($v->clean(12), '12', '->clean() checks that the value match the regex');

        try {
            $v->clean('symfony');
            $this->fail('->clean() throws an sfValidatorError if the value does not match the pattern');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the value does not match the pattern');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        $v = new sfValidatorRegex(array('pattern' => '/^[0-9]+$/', 'must_match' => false));
        $this->is($v->clean('symfony'), 'symfony', '->clean() checks that the value does not match the regex if must_match is false');

        try {
            $v->clean(12);
            $this->fail('->clean() throws an sfValidatorError if the value matches the pattern if must_match is false');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the value matches the pattern if must_match is false');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }

        $v = new sfValidatorRegex(array('pattern' => new sfCallable('generate_regex')));

        try {
            $v->clean('123');
            $this->pass('->clean() uses the pattern returned by a sfCallable pattern option');
        } catch (sfValidatorError $e) {
            $this->fail('->clean() uses the pattern returned by a sfCallable pattern option');
        }

        // ->asString()
        $this->diag('->asString()');

        $v = new sfValidatorRegex(array('pattern' => '/^[0-9]+$/', 'must_match' => false));
        $this->is($v->asString(), 'Regex({ must_match: false, pattern: \'/^[0-9]+$/\' })', '->asString() returns a string representation of the validator');

        // ->getPattern()
        $this->diag('->getPattern()');

        $v = new sfValidatorRegex(array('pattern' => '/\w+/'));
        $this->is($v->getPattern(), '/\w+/', '->getPattern() returns the regular expression');
        $v = new sfValidatorRegex(array('pattern' => new sfCallable('generate_regex')));
        $this->is($v->getPattern(), '/^123$/', '->getPattern() returns a regular expression from a sfCallable');
    }
}
