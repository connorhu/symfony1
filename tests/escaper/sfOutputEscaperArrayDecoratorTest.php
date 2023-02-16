<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../lib/helper/EscapingHelper.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfOutputEscaperArrayDecoratorTest extends TestCase
{
    private $escaped;

    protected function setUp(): void
    {
        sfConfig::set('sf_charset', 'UTF-8');

        $a = array('<strong>escaped!</strong>', 1, null, array(2, '<strong>escaped!</strong>'));
        $this->escaped = sfOutputEscaper::escape('esc_entities', $a);
    }

    public function testRaw()
    {
        $this->assertSame('<strong>escaped!</strong>', $this->escaped->getRaw(0), '->getRaw() returns the raw value');
    }

    public function testArrayAccessInterface()
    {
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $this->escaped[0], 'The escaped object behaves like an array');
        $this->assertSame(null, $this->escaped[2], 'The escaped object behaves like an array');
        $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $this->escaped[3][1], 'The escaped object behaves like an array');

        $this->assertSame(true, isset($this->escaped[1]), 'The escaped object behaves like an array (isset)');
    }

    public function testArrayAccessInterfaceReadOnlyOnUnset()
    {
        $this->expectException(sfException::class);

        unset($this->escaped[0]);
    }

    public function testArrayAccessInterfaceReadOnlyOnWrite()
    {
        $this->expectException(sfException::class);

        $this->escaped[0] = 12;
    }

    public function testIteratorInterface()
    {
        foreach ($this->escaped as $key => $value) {
            switch ($key) {
                case 0:
                    $this->assertSame('&lt;strong&gt;escaped!&lt;/strong&gt;', $value, 'The escaped object behaves like an array');
                    break;
                case 1:
                    $this->assertSame(1, $value, 'The escaped object behaves like an array');
                    break;
                case 2:
                    $this->assertSame(null, $value, 'The escaped object behaves like an array');
                    break;
                case 3:
                    break;
                default:
                    throw new \RuntimeException('The escaped object behaves like an array');
            }
        }
    }

    public function testValid()
    {
        $escaped = sfOutputEscaper::escape('esc_entities', array(1, 2, 3));
        $this->assertSame(true, $escaped->valid(), '->valid() returns true if called before iteration');
    }
}
