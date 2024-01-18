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
require_once __DIR__.'/../fixtures/MethodFilterer.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfClassManipulatorTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $source = <<<'EOF'
        <?php
        
        class Foo
        {
          function foo()
          {
            if (true)
            {
              return;
            }
          }
        
          function baz()
          {
            if (true)
            {
              return;
            }
          }
        }
        EOF;

        $sourceWithCodeBefore = <<<'EOF'
        <?php
        
        class Foo
        {
          function foo()
          {
            // code before
            if (true)
            {
              return;
            }
          }
        
          function baz()
          {
            if (true)
            {
              return;
            }
          }
        }
        EOF;

        $sourceWithCodeAfter = <<<'EOF'
        <?php
        
        class Foo
        {
          function foo()
          {
            if (true)
            {
              return;
            }
            // code after
          }
        
          function baz()
          {
            if (true)
            {
              return;
            }
          }
        }
        EOF;

        $sourceWithCodeBeforeAndAfter = <<<'EOF'
        <?php
        
        class Foo
        {
          function foo()
          {
            // code before
            if (true)
            {
              return;
            }
            // code after
          }
        
          function baz()
          {
            if (true)
            {
              return;
            }
          }
        }
        EOF;

        // ->wrapMethod()
        $this->diag('->wrapMethod()');
        $m = new sfClassManipulator($source);
        $this->is(fix_linebreaks($m->wrapMethod('bar', '// code before', '// code after')), fix_linebreaks($source), '->wrapMethod() does nothing if the method does not exist.');
        $m = new sfClassManipulator($source);
        $this->is(fix_linebreaks($m->wrapMethod('foo', '// code before')), fix_linebreaks($sourceWithCodeBefore), '->wrapMethod() adds code before the beginning of a method.');
        $m = new sfClassManipulator($source);
        $this->is(fix_linebreaks($m->wrapMethod('foo', '', '// code after')), fix_linebreaks($sourceWithCodeAfter), '->wrapMethod() adds code after the end of a method.');
        $this->is(fix_linebreaks($m->wrapMethod('foo', '// code before')), fix_linebreaks($sourceWithCodeBeforeAndAfter), '->wrapMethod() adds code to the previously manipulated code.');

        // ->getCode()
        $this->diag('->getCode()');
        $m = new sfClassManipulator($source);
        $this->is(fix_linebreaks($m->getCode()), fix_linebreaks($source), '->getCode() returns the source code when no manipulations has been done');
        $m->wrapMethod('foo', '', '// code after');
        $this->is(fix_linebreaks($m->getCode()), fix_linebreaks($sourceWithCodeAfter), '->getCode() returns the modified code');

        // ->setFile() ->getFile()
        $this->diag('->setFile() ->getFile()');
        $m = new sfClassManipulator($source);
        $m->setFile('foo');
        $this->is($m->getFile(), 'foo', '->setFile() sets the name of the file associated with the source code');

        // ::fromFile()
        $this->diag('::fromFile()');
        $file = sys_get_temp_dir().'/sf_tmp.php';
        file_put_contents($file, $source);
        $m = sfClassManipulator::fromFile($file);
        $this->is($m->getFile(), $file, '::fromFile() sets the file internally');

        // ->save()
        $this->diag('->save()');
        $m = sfClassManipulator::fromFile($file);
        $m->wrapMethod('foo', '', '// code after');
        $m->save();
        $this->is(fix_linebreaks(file_get_contents($file)), fix_linebreaks($sourceWithCodeAfter), '->save() saves the modified code if a file is associated with the instance');

        unlink($file);

        // ->filterMethod()
        $this->diag('->filterMethod()');

        $f = new MethodFilterer();

        $sourceFiltered = <<<'EOF'
        <?php
        
        class Foo
        {
          function foo($arg)
          {
            if (false)
            {
              return;
            }
          }
        
          function baz()
          {
            if (true)
            {
              return;
            }
          }
        }
        EOF;

        $sourceCRLF = str_replace("\n", "\r\n", $source);
        $sourceFilteredCRLF = str_replace("\n", "\r\n", $sourceFiltered);
        $sourceLF = str_replace("\n", "\n", $source);
        $sourceFilteredLF = str_replace("\n", "\n", $sourceFiltered);

        // CRLF
        $this->diag('CRLF');

        $m = new sfClassManipulator($sourceCRLF);
        $f->lines = array();
        $m->filterMethod('foo', array($f, 'filter1'));
        $this->is($m->getCode(), $sourceCRLF, '->filterMethod() does not change the code if the filter does nothing');
        $this->is_deeply($f->lines, array(
            "  function foo()\r\n",
            "  {\r\n",
            "    if (true)\r\n",
            "    {\r\n",
            "      return;\r\n",
            "    }\r\n",
            '  }',
        ), '->filterMethod() filters each line of the method');
        $m->filterMethod('foo', array($f, 'filter2'));
        $this->is($m->getCode(), $sourceFilteredCRLF, '->filterMethod() modifies the method');

        // LF
        $this->diag('LF');

        $m = new sfClassManipulator($sourceLF);
        $f->lines = array();
        $m->filterMethod('foo', array($f, 'filter1'));
        $this->is($m->getCode(), $sourceLF, '->filterMethod() does not change the code if the filter does nothing');
        $this->is_deeply($f->lines, array(
            "  function foo()\n",
            "  {\n",
            "    if (true)\n",
            "    {\n",
            "      return;\n",
            "    }\n",
            '  }',
        ), '->filterMethod() filters each line of the method');
        $m->filterMethod('foo', array($f, 'filter2'));
        $this->is($m->getCode(), $sourceFilteredLF, '->filterMethod() modifies the method');

        // no EOL
        $this->diag('no EOL');

        $sourceFlat = '<?php class Foo { function foo() { if (true) { return; } } function baz() { if (true) { return; } } }';
        $m = new sfClassManipulator($sourceFlat);
        $f->lines = array();
        $m->filterMethod('foo', array($f, 'filter1'));
        $this->is_deeply($f->lines, array('function foo() { if (true) { return; } }'), '->filterMethod() works when there are no line breaks');
        $this->is($m->getCode(), $sourceFlat, '->filterMethod() works when there are no line breaks');

        // mixed EOL
        $this->diag('mixed EOL');

        $sourceMixed = "<?php\r\n\nclass Foo\r\n{\n  function foo()\r\n  {\n    if (true)\r\n    {\n      return;\r\n    }\n  }\r\n\n  function baz()\r\n  {\n    if (true)\r\n    {\n      return;\r\n    }\n  }\r\n}";
        $m = new sfClassManipulator($sourceMixed);
        $f->lines = array();
        $m->filterMethod('foo', array($f, 'filter1'));
        $this->is_deeply($f->lines, array(
            "  function foo()\r\n",
            "  {\n",
            "    if (true)\r\n",
            "    {\n",
            "      return;\r\n",
            "    }\n",
            '  }',
        ), '->filterMethod() filters each line of a mixed EOL-style method');
    }
}
