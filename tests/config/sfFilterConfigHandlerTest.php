<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class sfFilterConfigHandlerTest extends TestCase
{
    public function testClassSectionParserError()
    {
        $handler = new sfFilterConfigHandler();
        $handler->initialize();

        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfFilterConfigHandler'.DIRECTORY_SEPARATOR;

        $files = array(
            $dir.'no_class.yml',
        );

        $this->expectException(sfParseException::class);
        $data = $handler->execute($files);
    }

    /** @dataProvider parseErrorsDataProvider */
    public function testParseErrors(string $errorKind)
    {
        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfFilterConfigHandler'.DIRECTORY_SEPARATOR;

        $handler = new sfFilterConfigHandler();
        $handler->initialize();

        $files = array(
            $dir.sprintf('no_%s.yml', $errorKind),
        );

        $this->expectException(sfParseException::class);
        $data = $handler->execute($files);
    }

    public function parseErrorsDataProvider(): Generator
    {
        yield array('execution');
        yield array('rendering');
    }

    public function testFilterInheritance()
    {
        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfFilterConfigHandler'.DIRECTORY_SEPARATOR;

        $handler = new sfFilterConfigHandler();
        $handler->initialize();

        $files = array(
            $dir.'default_filters.yml',
            $dir.'not_disabled.yml',
        );

        $this->expectException(sfConfigurationException::class);
        $data = $handler->execute($files);
    }

    public function testFilterDisabling()
    {
        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfFilterConfigHandler'.DIRECTORY_SEPARATOR;

        $handler = new sfFilterConfigHandler();
        $handler->initialize();

        $files = array(
            $dir.'disable.yml',
        );

        $this->assertDoesNotMatchRegularExpression('/defaultFilterClass/', $handler->execute($files), 'you can disable a filter by settings "enabled" to false');
    }

    public function testConditionSupport()
    {
        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfFilterConfigHandler'.DIRECTORY_SEPARATOR;

        $handler = new sfFilterConfigHandler();
        $handler->initialize();

        $files = array(
            $dir.'condition.yml',
        );

        sfConfig::set('default_test', true);
        $this->assertMatchesRegularExpression('/defaultFilterClass/', $handler->execute($files), 'you can add a "condition" key to the filter parameters');

        sfConfig::set('default_test', false);
        $this->assertDoesNotMatchRegularExpression('/defaultFilterClass/', $handler->execute($files), 'you can add a "condition" key to the filter parameters');
    }

    public function testUsualConfiuration()
    {
        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfFilterConfigHandler'.DIRECTORY_SEPARATOR;

        $handler = new sfFilterConfigHandler();
        $handler->initialize();

        $files = array(
            $dir.'default_filters.yml',
            $dir.'filters.yml',
        );

        $data = $handler->execute($files);
        $data = preg_replace('#date\: \d+/\d+/\d+ \d+\:\d+\:\d+\n#', '', $data);
        $this->assertSame($data, str_replace("\r\n", "\n", file_get_contents($dir.'result.php')), 'core filters.yml can be overriden');
    }
}
