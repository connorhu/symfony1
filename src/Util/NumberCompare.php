<?php

namespace Symfony1\Components\Util;

use Symfony1\Components\Exception\Exception;
use function preg_match;
use function sprintf;
use function array_key_exists;
use function strtolower;
/**
* Numeric comparisons.
*
* sfNumberCompare compiles a simple comparison to an anonymous
subroutine, which you can call with a value to be tested again.
*
* Now this would be very pointless, if sfNumberCompare didn't understand
magnitudes.
*
* The target value may use magnitudes of kilobytes (k, ki),
megabytes (m, mi), or gigabytes (g, gi).  Those suffixed
with an i use the appropriate 2**n version in accordance with the
IEC standard: http://physics.nist.gov/cuu/Units/binary.html
*
* based on perl Number::Compare module.
*
* @author Fabien Potencier <fabien.potencier@gmail.com> php port
* @author Richard Clamp <richardc@unixbeard.net> perl version
* @copyright 2004-2005 Fabien Potencier <fabien.potencier@gmail.com>
* @copyright 2002 Richard Clamp <richardc@unixbeard.net>
*
* @see http://physics.nist.gov/cuu/Units/binary.html
*
* @version SVN: $Id$
*/
class NumberCompare
{
    protected $test = '';
    public function __construct($test)
    {
        $this->test = $test;
    }
    public function test($number)
    {
        if (!preg_match('{^([<>]=?)?(.*?)([kmg]i?)?$}i', $this->test, $matches)) {
            throw new Exception(sprintf('don\'t understand "%s" as a test.', $this->test));
        }
        $target = array_key_exists(2, $matches) ? $matches[2] : '';
        $magnitude = array_key_exists(3, $matches) ? $matches[3] : '';
        if ('k' === strtolower($magnitude)) {
            $target *= 1000;
        }
        if ('ki' === strtolower($magnitude)) {
            $target *= 1024;
        }
        if ('m' === strtolower($magnitude)) {
            $target *= 1000000;
        }
        if ('mi' === strtolower($magnitude)) {
            $target *= 1024 * 1024;
        }
        if ('g' === strtolower($magnitude)) {
            $target *= 1000000000;
        }
        if ('gi' === strtolower($magnitude)) {
            $target *= 1024 * 1024 * 1024;
        }
        $comparison = array_key_exists(1, $matches) ? $matches[1] : '==';
        if ('==' === $comparison || '' == $comparison) {
            return $number == $target;
        }
        if ('>' === $comparison) {
            return $number > $target;
        }
        if ('>=' === $comparison) {
            return $number >= $target;
        }
        if ('<' === $comparison) {
            return $number < $target;
        }
        if ('<=' === $comparison) {
            return $number <= $target;
        }
        return false;
    }
}
class_alias(NumberCompare::class, 'sfNumberCompare', false);