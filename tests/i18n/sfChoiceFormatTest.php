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
class sfChoiceFormatTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $n = new sfChoiceFormat();

        $strings = array(
            array(
                '[1,2] accepts values between 1 and 2, inclusive',
                array(
                    array('[1,2]'),
                    array('accepts values between 1 and 2, inclusive'),
                ),
            ),

            array(
                '(1,2) accepts values between 1 and 2, excluding 1 and 2',
                array(
                    array('(1,2)'),
                    array('accepts values between 1 and 2, excluding 1 and 2'),
                ),
            ),

            array(
                '{1,2,3,4} only values defined in the set are accepted',
                array(
                    array('{1,2,3,4}'),
                    array('only values defined in the set are accepted'),
                ),
            ),

            array(
                '[-Inf,0) accepts value greater or equal to negative infinity and strictly less than 0',
                array(
                    array('[-Inf,0)'),
                    array('accepts value greater or equal to negative infinity and strictly less than 0'),
                ),
            ),

            array(
                '[0] no file|[1] one file|(1,Inf] {number} files',
                array(
                    array('[0]', '[1]', '(1,Inf]'),
                    array('no file', 'one file', '{number} files'),
                ),
            ),
        );

        // ->parse()
        $this->diag('->parse()');
        foreach ($strings as $string) {
            $this->is($n->parse($string[0]), $string[1], '->parse() takes a choice strings as its first parameters');
        }

        // ->isValid()
        $this->diag('->isValid()');
        $this->is($n->isValid(1, '[1]'), true, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(2, '[1]'), false, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(1, '(1)'), false, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(1, '(1,10)'), false, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(10, '(1,10)'), false, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(4, '(1,10)'), true, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(1, '{1,2,4,5}'), true, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(3, '{1,2,4,5}'), false, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(4, '{1,2,4,5}'), true, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(1, '[0,+Inf]'), true, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(10000000, '[0,+Inf]'), true, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(10000000, '[0,Inf]'), true, '->isValid() determines if a given number belongs to the given set');
        $this->is($n->isValid(-10000000, '[-Inf,+Inf]'), true, '->isValid() determines if a given number belongs to the given set');

        try {
            $n->isValid(1, '[1');
            $this->fail('->isValid() throw an exception if the set is not valid');
        } catch (sfException $e) {
            $this->pass('->isValid() throw an exception if the set is not valid');
        }

        // ->format()
        $this->diag('->format()');
        $this->is($n->format($strings[0][0], 1), $strings[0][1][1][0], '->format() returns the string that match the number');
        $this->is($n->format($strings[0][0], 4), false, '->format() returns the string that match the number');
        $this->is($n->format($strings[4][0], 0), $strings[4][1][1][0], '->format() returns the string that match the number');
        $this->is($n->format($strings[4][0], 1), $strings[4][1][1][1], '->format() returns the string that match the number');
        $this->is($n->format($strings[4][0], 12), $strings[4][1][1][2], '->format() returns the string that match the number');

        // test strings with some set notation
        $this->is($n->format('[0]Some text|[1,Inf] Some text (10)', 12), 'Some text (10)', '->format() does not take into account ranges that are not prefixed with |');

        // test set notation
        // tests adapted from Prado unit test suite
        $this->diag('set notation');
        $string = '{n: n%2 == 0} are even numbers |{n: n >= 5} are not even and greater than or equal to 5';
        $this->is($n->format($string, 0), 'are even numbers', '->format() can takes a set notation in the format string');
        $this->is($n->format($string, 2), 'are even numbers', '->format() can takes a set notation in the format string');
        $this->is($n->format($string, 4), 'are even numbers', '->format() can takes a set notation in the format string');
        $this->isnt($n->format($string, 1), 'are even numbers', '->format() can takes a set notation in the format string');
        $this->is($n->format($string, 5), 'are not even and greater than or equal to 5', '->format() can takes a set notation in the format string');

        $this->diag('set notation for polish');
        $string = '[1] plik |{2,3,4} pliki |[5,21] pliko\'w |{n: n % 10 > 1 && n %10 < 5} pliki |{n: n%10 >= 5 || n%10 <=1} pliko\'w';
        $wants = array(
            'plik' => array(1),
            'pliki' => array(2, 3, 4, 22, 23, 24),
            'pliko\'w' => array(5, 6, 7, 11, 12, 15, 17, 20, 21, 25, 26, 30),
        );
        foreach ($wants as $want => $numbers) {
            foreach ($numbers as $number) {
                $this->is($n->format($string, $number), $want, '->format() can deal with polish!');
            }
        }

        $this->diag('set notation for russian');
        $string = '
        {n: n % 10 == 1 && n % 100 != 11} test1
        |{n: n % 10 >= 2 && n % 10 <= 4 && ( n % 100 < 10 || n % 100 >= 20 )} test2
        |{n: 2} test3';

        $wants = array(
            'test1' => array(1, 21, 31, 41),
            'test2' => array(2, 4, 22, 24, 32, 34),
            'test3' => array(0, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 20, 25, 26, 30),
        );
        foreach ($wants as $want => $numbers) {
            foreach ($numbers as $number) {
                $this->is($n->format($string, $number), $want, '->format() can deal with russian!');
            }
        }

        $this->diag('set notation for english');
        $string = '[0] none |{n: n % 10 == 1} 1st |{n: n % 10 == 2} 2nd |{n: n % 10 == 3} 3rd |{n:n} th';

        $wants = array(
            'none' => array(0),
            '1st' => array(1, 11, 21),
            '2nd' => array(2, 12, 22),
            '3rd' => array(3, 13, 23),
            'th' => array(4, 5, 6, 7, 14, 15),
        );
        foreach ($wants as $want => $numbers) {
            foreach ($numbers as $number) {
                $this->is($n->format($string, $number), $want, '->format() can deal with english!');
            }
        }
    }
}
