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
require_once __DIR__.'/../fixtures/A.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfYamlParserTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        sfYaml::setSpecVersion('1.1');

        $parser = new sfYamlParser();

        $path = __DIR__.'/../fixtures/yaml';
        $files = $parser->parse(file_get_contents($path.'/index.yml'));
        foreach ($files as $file) {
            $this->diag($file);

            $yamls = file_get_contents($path.'/'.$file.'.yml');

            // split YAMLs documents
            foreach (preg_split('/^---( %YAML\:1\.0)?/m', $yamls) as $yaml) {
                if (!$yaml) {
                    continue;
                }

                $test = $parser->parse($yaml);
                if (isset($test['todo']) && $test['todo']) {
                    // TODO ? $this->todo($test['test']);
                } else {
                    $expected = var_export(eval('return '.trim($test['php']).';'), true);

                    $this->is(var_export($parser->parse($test['yaml']), true), $expected, $test['test']);
                }
            }
        }

        // test tabs in YAML
        $yamls = array(
            "foo:\n	bar",
            "foo:\n 	bar",
            "foo:\n	 bar",
            "foo:\n 	 bar",
        );

        foreach ($yamls as $yaml) {
            try {
                $content = $parser->parse($yaml);
                $this->fail('YAML files must not contain tabs');
            } catch (InvalidArgumentException $e) {
                $this->pass('YAML files must not contain tabs');
            }
        }

        $yaml = <<<'EOF'
        --- %YAML:1.0
        foo
        ...
        EOF;

        $this->is('foo', $parser->parse($yaml));

        // objects
        $this->diag('Objects support');
        $a = array('foo' => new A(), 'bar' => 1);

        $object = $parser->parse(<<<'EOF'
        foo: !!php/object:O:1:"A":1:{s:1:"a";s:3:"foo";}
        bar: 1
        EOF
        );
        $this->ok($object['foo'] instanceof A, '->parse() is able to dump objects');
        $this->is($object['bar'], 1, '->parse() is able to dump objects');
        $this->is($object['foo']->a, 'foo', '->parse() is able to dump objects');
    }
}
