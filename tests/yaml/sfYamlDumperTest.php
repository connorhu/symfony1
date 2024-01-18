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
class sfYamlDumperTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        sfYaml::setSpecVersion('1.1');

        $parser = new sfYamlParser();
        $dumper = new sfYamlDumper();

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
                if (isset($test['dump_skip']) && $test['dump_skip']) {
                    continue;
                }
                if (isset($test['todo']) && $test['todo']) {
                    // TODO ? $this->todo($test['test']);
                } else {
                    $expected = eval('return '.trim($test['php']).';');

                    $this->is_deeply($parser->parse($dumper->dump($expected, 10)), $expected, $test['test']);
                }
            }
        }

        // inline level
        $array = array(
            '' => 'bar',
            'foo' => '#bar',
            'foo\'bar' => array(),
            'bar' => array(1, 'foo'),
            'foobar' => array(
                'foo' => 'bar',
                'bar' => array(1, 'foo'),
                'foobar' => array(
                    'foo' => 'bar',
                    'bar' => array(1, 'foo'),
                ),
            ),
        );

        $expected = <<<'EOF'
        { '': bar, foo: '#bar', 'foo''bar': {  }, bar: [1, foo], foobar: { foo: bar, bar: [1, foo], foobar: { foo: bar, bar: [1, foo] } } }
        EOF;
        $this->is($dumper->dump($array, -10), $expected, '->dump() takes an inline level argument');
        $this->is($dumper->dump($array, 0), $expected, '->dump() takes an inline level argument');

        $expected = <<<'EOF'
        '': bar
        foo: '#bar'
        'foo''bar': {  }
        bar: [1, foo]
        foobar: { foo: bar, bar: [1, foo], foobar: { foo: bar, bar: [1, foo] } }
        
        EOF;
        $this->is($dumper->dump($array, 1), $expected, '->dump() takes an inline level argument');

        $expected = <<<'EOF'
        '': bar
        foo: '#bar'
        'foo''bar': {  }
        bar:
          - 1
          - foo
        foobar:
          foo: bar
          bar: [1, foo]
          foobar: { foo: bar, bar: [1, foo] }
        
        EOF;
        $this->is($dumper->dump($array, 2), $expected, '->dump() takes an inline level argument');

        $expected = <<<'EOF'
        '': bar
        foo: '#bar'
        'foo''bar': {  }
        bar:
          - 1
          - foo
        foobar:
          foo: bar
          bar:
            - 1
            - foo
          foobar:
            foo: bar
            bar: [1, foo]
        
        EOF;
        $this->is($dumper->dump($array, 3), $expected, '->dump() takes an inline level argument');

        $expected = <<<'EOF'
        '': bar
        foo: '#bar'
        'foo''bar': {  }
        bar:
          - 1
          - foo
        foobar:
          foo: bar
          bar:
            - 1
            - foo
          foobar:
            foo: bar
            bar:
              - 1
              - foo
        
        EOF;
        $this->is($dumper->dump($array, 4), $expected, '->dump() takes an inline level argument');
        $this->is($dumper->dump($array, 10), $expected, '->dump() takes an inline level argument');

        // objects
        $this->diag('Objects support');
        $a = array('foo' => new A(), 'bar' => 1);
        $this->is($dumper->dump($a), '{ foo: !!php/object:O:1:"A":1:{s:1:"a";s:3:"foo";}, bar: 1 }', '->dump() is able to dump objects');
    }
}
