<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait PhpUnitSfTestHelperTrait
{
    public function diag($message): void {}

    /**
     * @see self::assertSame
     * @deprecated
     */
    public function is($actual, $expected, string $message = ''): void
    {
        $this->assertSame($expected, $actual, $message);
    }

    /**
     * @see self::assertNotSame
     * @deprecated
     */
    public function isnt($actual, $expected, string $message = ''): void
    {
        $this->assertNotSame($expected, $actual, $message);
    }

    public function is_deeply($actual, $expected, string $message = ''): void
    {
        $this->assertSame($expected, $actual, $message);
    }

    public function pass($message): void
    {
        $this->assertSame(true, true, $message);
    }

    public function ok($actual, string $message = ''): void
    {
        $this->assertTrue($actual, $message);
    }

    public function isa_ok($actual, $expected, string $message = ''): void
    {
        if ('array' === $expected) {
            $this->assertIsArray($actual, $message);
        } elseif (strpos($expected, 'sf') === 0) {
            $this->assertInstanceOf($expected, $actual, $message);
        } else {
            throw new \RuntimeException('unhandled type: '.$expected);
        }
    }

    public function like($exp, $regex, string $message = '')
    {
        return $this->ok(preg_match($regex, $exp) > 0, $message);
    }

    public function todo() {}
}
