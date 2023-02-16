<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Idea from Symfony's http foundation component.
 *
 * @internal
 *
 * @coversNothing
 */
trait sfInternalServerBasedFixtureTestTrait
{
    private static $server;

    public static function setUpBeforeClass(): void
    {
        $descriptorPipes = array(
            1 => array('file', '/dev/null', 'w'),
            2 => array('file', '/dev/null', 'w'),
        );
        self::$server = proc_open('exec '.\PHP_BINARY.' -S localhost:8300', $descriptorPipes, $pipes, self::$fixtureDirectory);

        sleep(1);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$server) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
    }

    /**
     * @dataProvider provideFixtures
     */
    public function testFixtures($fixture)
    {
        $context = stream_context_create(array());
        $result = file_get_contents(sprintf('http://localhost:8300/%s.php', $fixture), false, $context);
        $result = preg_replace_callback('/expires=[^;]++/', fn ($m) => str_replace('-', ' ', $m[0]), $result);

        $expectedFilename = sprintf(self::$fixtureDirectory.'/%s.expected', $fixture);
        $notExpectedFilename = sprintf(self::$fixtureDirectory.'/%s.not_expected', $fixture);

        if (is_file($expectedFilename)) {
            $this->assertStringEqualsFile($expectedFilename, $result, $fixture);
        } elseif (is_file($notExpectedFilename)) {
            $this->assertStringNotEqualsFile($notExpectedFilename, $result, $fixture);
        } else {
            throw new \RuntimeException('Missing expected and not expected file.');
        }
    }

    public function provideFixtures(): Generator
    {
        foreach (glob(self::$fixtureDirectory.'/*.php') as $file) {
            yield array(pathinfo($file, \PATHINFO_FILENAME));
        }
    }
}
