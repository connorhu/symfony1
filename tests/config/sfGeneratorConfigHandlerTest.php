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
class sfGeneratorConfigHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        sfConfig::set('sf_symfony_lib_dir', realpath(__DIR__.'/../../../lib'));
    }

    public function testParseError()
    {
        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfGeneratorConfigHandler'.DIRECTORY_SEPARATOR;

        $handler = new sfGeneratorConfigHandler();
        $handler->initialize();

        $files = array(
            $dir.'empty.yml',
            $dir.'no_generator_class.yml',
        );

        $this->expectException(sfParseException::class);
        $this->expectExceptionMessageMatches('/must specify a generator class section under the generator section/');
        $data = $handler->execute($files);
    }

    public function testMissingGeneratorSection()
    {
        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfGeneratorConfigHandler'.DIRECTORY_SEPARATOR;

        $handler = new sfGeneratorConfigHandler();
        $handler->initialize();

        $files = array(
            $dir.'empty.yml',
            $dir.'no_generator_section.yml',
        );

        $this->expectException(sfParseException::class);
        $this->expectExceptionMessageMatches('/must specify a generator section/');
        $data = $handler->execute($files);
    }

    public function testFieldsSectionUnderParam()
    {
        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfGeneratorConfigHandler'.DIRECTORY_SEPARATOR;

        $handler = new sfGeneratorConfigHandler();
        $handler->initialize();

        $files = array(
            $dir.'empty.yml',
            $dir.'root_fields_section.yml',
        );

        $this->expectException(sfParseException::class);
        $this->expectExceptionMessageMatches('/can specify a "fields" section but only under the param section/');
        $data = $handler->execute($files);
    }

    public function testListSectionUnderParam()
    {
        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfGeneratorConfigHandler'.DIRECTORY_SEPARATOR;

        $handler = new sfGeneratorConfigHandler();
        $handler->initialize();

        $files = array(
            $dir.'empty.yml',
            $dir.'root_list_section.yml',
        );

        $this->expectException(sfParseException::class);
        $this->expectExceptionMessageMatches('/can specify a "list" section but only under the param section/');
        $data = $handler->execute($files);
    }

    public function testEditSectionUnderParam()
    {
        $fixtureDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixtureDir.DIRECTORY_SEPARATOR.'sfGeneratorConfigHandler'.DIRECTORY_SEPARATOR;

        $handler = new sfGeneratorConfigHandler();
        $handler->initialize();

        $files = array(
            $dir.'empty.yml',
            $dir.'root_edit_section.yml',
        );

        $this->expectException(sfParseException::class);
        $this->expectExceptionMessageMatches('/can specify a "edit" section but only under the param section/');
        $data = $handler->execute($files);
    }
}
