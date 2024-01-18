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
class sfNumberFormatInfoTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // __construct()
        $this->diag('__construct()');
        try {
            $c = new sfNumberFormatInfo();
            $this->fail('__construct() takes a mandatory ICU array as its first argument');
        } catch (sfException $e) {
            $this->pass('__construct() takes a mandatory ICU array as its first argument');
        }

        // ::getInstance()
        $this->diag('::getInstance()');
        $this->isa_ok(sfNumberFormatInfo::getInstance(), 'sfNumberFormatInfo', '::getInstance() returns an sfNumberFormatInfo instance');
        $c = sfCultureInfo::getInstance();
        $this->is(sfNumberFormatInfo::getInstance($c), $c->getNumberFormat(), '::getInstance() can take a sfCultureInfo instance as its first argument');
        $this->isa_ok(sfNumberFormatInfo::getInstance('fr'), 'sfNumberFormatInfo', '::getInstance() can take a culture as its first argument');
        $n = sfNumberFormatInfo::getInstance();
        $n->setPattern(sfNumberFormatInfo::PERCENTAGE);
        $this->is(sfNumberFormatInfo::getInstance(null, sfNumberFormatInfo::PERCENTAGE)->getPattern(), $n->getPattern(), '::getInstance() can take a formatting type as its second argument');

        // ->getPattern() ->setPattern()
        $this->diag('->getPattern() ->setPattern()');
        $n = sfNumberFormatInfo::getInstance();
        $n1 = sfNumberFormatInfo::getInstance();
        $n->setPattern(sfNumberFormatInfo::CURRENCY);
        $pattern = $n->getPattern();
        $n1->setPattern(sfNumberFormatInfo::PERCENTAGE);
        $pattern1 = $n1->getPattern();
        $this->isnt($pattern, $pattern1, '->getPattern() ->setPattern() changes the current pattern');

        $n = sfNumberFormatInfo::getInstance();
        $n1 = sfNumberFormatInfo::getInstance();
        $n->Pattern = sfNumberFormatInfo::CURRENCY;
        $n1->setPattern(sfNumberFormatInfo::CURRENCY);
        $this->is($n->getPattern(), $n1->getPattern(), '->setPattern() is equivalent to ->Pattern = ');
        $this->is($n->getPattern(), $n->Pattern, '->getPattern() is equivalent to ->Pattern');

        // ::getCurrencyInstance()
        $this->diag('::getCurrencyInstance()');
        $this->is(sfNumberFormatInfo::getCurrencyInstance()->getPattern(), sfNumberFormatInfo::getInstance(null, sfNumberFormatInfo::CURRENCY)->getPattern(), '::getCurrencyInstance() is a shortcut for ::getInstance() and type sfNumberFormatInfo::CURRENCY');

        // ::getPercentageInstance()
        $this->diag('::getPercentageInstance()');
        $this->is(sfNumberFormatInfo::getPercentageInstance()->getPattern(), sfNumberFormatInfo::getInstance(null, sfNumberFormatInfo::PERCENTAGE)->getPattern(), '::getPercentageInstance() is a shortcut for ::getInstance() and type sfNumberFormatInfo::PERCENTAGE');

        // ::getScientificInstance()
        $this->diag('::getScientificInstance()');
        $this->is(sfNumberFormatInfo::getScientificInstance()->getPattern(), sfNumberFormatInfo::getInstance(null, sfNumberFormatInfo::SCIENTIFIC)->getPattern(), '::getScientificInstance() is a shortcut for ::getInstance() and type sfNumberFormatInfo::SCIENTIFIC');

        $tests = array(
            'fr' => array(
                'DecimalDigits' => -1,
                'DecimalSeparator' => ',',
                'GroupSeparator' => ' ',
                'CurrencySymbol' => '$US',
                'NegativeInfinitySymbol' => '-∞',
                'PositiveInfinitySymbol' => '+∞',
                'NegativeSign' => '-',
                'PositiveSign' => '+',
                'NaNSymbol' => 'NaN',
                'PercentSymbol' => '%',
                'PerMilleSymbol' => '‰',
            ),
            'en' => array(
                'DecimalDigits' => -1,
                'DecimalSeparator' => '.',
                'GroupSeparator' => ',',
                'CurrencySymbol' => '$',
                'NegativeInfinitySymbol' => '-∞',
                'PositiveInfinitySymbol' => '+∞',
                'NegativeSign' => '-',
                'PositiveSign' => '+',
                'NaNSymbol' => 'NaN',
                'PercentSymbol' => '%',
                'PerMilleSymbol' => '‰',
            ),
        );

        foreach ($tests as $culture => $fixtures) {
            $n = sfNumberFormatInfo::getInstance($culture);

            foreach ($fixtures as $method => $result) {
                $getter = 'get'.$method;
                $this->is($n->{$getter}(), $result, sprintf('->%s() returns "%s" for culture "%s"', $getter, $result, $culture));
            }
        }

        // setters/getters
        foreach (array(
            'DecimalDigits', 'DecimalSeparator', 'GroupSeparator',
            'CurrencySymbol', 'NegativeInfinitySymbol', 'PositiveInfinitySymbol',
            'NegativeSign', 'PositiveSign', 'NaNSymbol', 'PercentSymbol', 'PerMilleSymbol',
        ) as $method) {
            $this->diag(sprintf('->get%s() ->set%s()', $method, $method));
            $n = sfNumberFormatInfo::getInstance();
            $setter = 'set'.$method;
            $getter = 'get'.$method;
            $n->{$setter}('foo');
            $this->is($n->{$getter}(), 'foo', sprintf('->%s() sets the current decimal digits', $setter));
            $this->is($n->{$method}, $n->{$getter}(), sprintf('->%s() is equivalent to ->%s', $getter, $method));
            $n->{$method} = 'bar';
            $this->is($n->{$getter}(), 'bar', sprintf('->%s() is equivalent to ->%s = ', $setter, $method));
        }

        foreach (array('GroupSizes', 'NegativePattern', 'PositivePattern') as $method) {
            $this->diag(sprintf('->get%s() ->set%s()', $method, $method));
            $n = sfNumberFormatInfo::getInstance();
            $setter = 'set'.$method;
            $getter = 'get'.$method;
            $n->{$setter}(array('foo', 'foo'));
            $this->is($n->{$getter}(), array('foo', 'foo'), sprintf('->%s() sets the current decimal digits', $setter));
            $this->is($n->{$method}, $n->{$getter}(), sprintf('->%s() is equivalent to ->%s', $getter, $method));
            $n->{$method} = array('bar', 'bar');
            $this->is($n->{$getter}(), array('bar', 'bar'), sprintf('->%s() is equivalent to ->%s = ', $setter, $method));
        }
    }
}
