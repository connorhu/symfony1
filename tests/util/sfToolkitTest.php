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
class sfToolkitTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // ::stringToArray()
        $this->diag('::stringToArray()');
        $tests = array(
            'foo=bar' => array('foo' => 'bar'),
            'foo1=bar1 foo=bar   ' => array('foo1' => 'bar1', 'foo' => 'bar'),
            'foo1="bar1 foo1"' => array('foo1' => 'bar1 foo1'),
            'foo1="bar1 foo1" foo=bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
            'foo1 = "bar1=foo1" foo=bar' => array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
            'foo1= \'bar1 foo1\'    foo  =     bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
            'foo1=\'bar1=foo1\' foo = bar' => array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
            'foo1=  bar1 foo1 foo=bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
            'foo1="l\'autre" foo=bar' => array('foo1' => 'l\'autre', 'foo' => 'bar'),
            'foo1="l"autre" foo=bar' => array('foo1' => 'l"autre', 'foo' => 'bar'),
            'foo_1=bar_1' => array('foo_1' => 'bar_1'),
            'data-foo=bar' => array('data-foo' => 'bar'),
            'data-foo-bar=baz' => array('data-foo-bar' => 'baz'),
        );

        foreach ($tests as $string => $attributes) {
            $this->is(sfToolkit::stringToArray($string), $attributes, '->stringToArray()');
        }

        // ::isUTF8()
        $this->diag('::isUTF8()');
        $this->is(sfToolkit::isUTF8('été'), true, '::isUTF8() returns true if the parameter is an UTF-8 encoded string');
        $this->is(sfToolkit::isUTF8('AZERTYazerty1234-_'), true, '::isUTF8() returns true if the parameter is an UTF-8 encoded string');
        $this->is(sfToolkit::isUTF8('AZERTYazerty1234-_'.chr(254)), false, '::isUTF8() returns false if the parameter is not an UTF-8 encoded string');
        // check a very long string
        $string = str_repeat('Here is an UTF8 string avec du français.', 1000);
        $this->is(sfToolkit::isUTF8($string), true, '::isUTF8() can operate on very large strings');

        // ::literalize()
        $this->diag('::literalize()');
        foreach (array('true', 'on', '+', 'yes') as $param) {
            $this->is(sfToolkit::literalize($param), true, sprintf('::literalize() returns true with "%s"', $param));
            if (strtoupper($param) != $param) {
                $this->is(sfToolkit::literalize(strtoupper($param)), true, sprintf('::literalize() returns true with "%s"', strtoupper($param)));
            }
            $this->is(sfToolkit::literalize(' '.$param.' '), true, sprintf('::literalize() returns true with "%s"', ' '.$param.' '));
        }

        foreach (array('false', 'off', '-', 'no') as $param) {
            $this->is(sfToolkit::literalize($param), false, sprintf('::literalize() returns false with "%s"', $param));
            if (strtoupper($param) != $param) {
                $this->is(sfToolkit::literalize(strtoupper($param)), false, sprintf('::literalize() returns false with "%s"', strtoupper($param)));
            }
            $this->is(sfToolkit::literalize(' '.$param.' '), false, sprintf('::literalize() returns false with "%s"', ' '.$param.' '));
        }

        foreach (array('null', '~', '') as $param) {
            $this->is(sfToolkit::literalize($param), null, sprintf('::literalize() returns null with "%s"', $param));
            if (strtoupper($param) != $param) {
                $this->is(sfToolkit::literalize(strtoupper($param)), null, sprintf('::literalize() returns null with "%s"', strtoupper($param)));
            }
            $this->is(sfToolkit::literalize(' '.$param.' '), null, sprintf('::literalize() returns null with "%s"', ' '.$param.' '));
        }

        // ::replaceConstants()
        $this->diag('::replaceConstants()');
        sfConfig::set('foo', 'bar');
        $this->is(sfToolkit::replaceConstants('my value with a %foo% constant'), 'my value with a bar constant', '::replaceConstantsCallback() replaces constants enclosed in %');
        $this->is(sfToolkit::replaceConstants('%Y/%m/%d %H:%M'), '%Y/%m/%d %H:%M', '::replaceConstantsCallback() does not replace unknown constants');
        sfConfig::set('bar', null);
        $this->is(sfToolkit::replaceConstants('my value with a %bar% constant'), 'my value with a  constant', '::replaceConstantsCallback() replaces constants enclosed in % even if value is null');
        $this->is(sfToolkit::replaceConstants('my value with a %foobar% constant'), 'my value with a %foobar% constant', '::replaceConstantsCallback() returns the original string if the constant is not defined');
        $this->is(sfToolkit::replaceConstants('my value with a %foo\'bar% constant'), 'my value with a %foo\'bar% constant', '::replaceConstantsCallback() returns the original string if the constant is not defined');
        $this->is(sfToolkit::replaceConstants('my value with a %foo"bar% constant'), 'my value with a %foo"bar% constant', '::replaceConstantsCallback() returns the original string if the constant is not defined');

        // ::isPathAbsolute()
        $this->diag('::isPathAbsolute()');
        $this->is(sfToolkit::isPathAbsolute('/test'), true, '::isPathAbsolute() returns true if path is absolute');
        $this->is(sfToolkit::isPathAbsolute('\\test'), true, '::isPathAbsolute() returns true if path is absolute');
        $this->is(sfToolkit::isPathAbsolute('C:\\test'), true, '::isPathAbsolute() returns true if path is absolute');
        $this->is(sfToolkit::isPathAbsolute('d:/test'), true, '::isPathAbsolute() returns true if path is absolute');
        $this->is(sfToolkit::isPathAbsolute('test'), false, '::isPathAbsolute() returns false if path is relative');
        $this->is(sfToolkit::isPathAbsolute('../test'), false, '::isPathAbsolute() returns false if path is relative');
        $this->is(sfToolkit::isPathAbsolute('..\\test'), false, '::isPathAbsolute() returns false if path is relative');

        // ::stripComments()
        $this->diag('::stripComments()');

        $php = <<<'EOF'
        <?php
        
        # A perl like comment
        // Another comment
        /* A very long
        comment
        on several lines
        */
        
        $i = 1; // A comment on a PHP line
        EOF;

        $stripped_php = '<?php $i = 1; ';

        $this->is(preg_replace('/\s*(\r?\n)+/', ' ', sfToolkit::stripComments($php)), $stripped_php, '::stripComments() strip all comments from a php string');

        $php = <<<'EOF'
        <?php
          $pluginDirs = '/*/modules/lib/helper';
          $pluginDirs = '/*/lib/helper';
        
        EOF;

        $this->is(sfToolkit::stripComments($php), $php, '::stripComments() correctly handles comments within strings');

        // ::stripslashesDeep()
        $this->diag('::stripslashesDeep()');
        $this->is(sfToolkit::stripslashesDeep('foo'), 'foo', '::stripslashesDeep() strip slashes on string');
        $this->is(sfToolkit::stripslashesDeep(addslashes("foo's bar")), "foo's bar", '::stripslashesDeep() strip slashes on array');
        $this->is(sfToolkit::stripslashesDeep(array(addslashes("foo's bar"), addslashes("foo's bar"))), array("foo's bar", "foo's bar"), '::stripslashesDeep() strip slashes on deep arrays');
        $this->is(sfToolkit::stripslashesDeep(array(array('foo' => addslashes("foo's bar")), addslashes("foo's bar"))), array(array('foo' => "foo's bar"), "foo's bar"), '::stripslashesDeep() strip slashes on deep arrays');

        // ::clearDirectory()
        $this->diag('::clearDirectory()');
        $tmp_dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'symfony_tests_'.rand(1, 999);
        mkdir($tmp_dir);
        file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'test', 'ok');
        mkdir($tmp_dir.DIRECTORY_SEPARATOR.'foo');
        file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar', 'ok');
        sfToolkit::clearDirectory($tmp_dir);
        $this->ok(!is_dir($tmp_dir.DIRECTORY_SEPARATOR.'foo'), '::clearDirectory() removes all directories from the directory parameter');
        $this->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar'), '::clearDirectory() removes all directories from the directory parameter');
        $this->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'test'), '::clearDirectory() removes all directories from the directory parameter');
        rmdir($tmp_dir);

        // ::clearGlob()
        $this->diag('::clearGlob()');
        $tmp_dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'symfony_tests_'.rand(1, 999);
        mkdir($tmp_dir);
        mkdir($tmp_dir.DIRECTORY_SEPARATOR.'foo');
        mkdir($tmp_dir.DIRECTORY_SEPARATOR.'bar');
        file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar', 'ok');
        file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'foo', 'ok');
        file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'bar'.DIRECTORY_SEPARATOR.'bar', 'ok');
        sfToolkit::clearGlob($tmp_dir.'/*/bar');
        $this->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar'), '::clearGlob() removes all files and directories matching the pattern parameter');
        $this->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar'), '::clearGlob() removes all files and directories matching the pattern parameter');
        $this->ok(is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'foo'), '::clearGlob() removes all files and directories matching the pattern parameter');
        sfToolkit::clearDirectory($tmp_dir);
        rmdir($tmp_dir);

        // ::arrayDeepMerge()
        $this->diag('::arrayDeepMerge()');
        $this->is(
            sfToolkit::arrayDeepMerge(array('d' => 'due', 't' => 'tre'), array('d' => 'bis', 'q' => 'quattro')),
            array('d' => 'bis', 't' => 'tre', 'q' => 'quattro'),
            '::arrayDeepMerge() merges linear arrays preserving literal keys'
        );
        $this->is(
            sfToolkit::arrayDeepMerge(array('d' => 'due', 't' => 'tre', 'c' => array('c' => 'cinco')), array('d' => array('due', 'bis'), 'q' => 'quattro', 'c' => array('c' => 'cinque', 'c2' => 'cinco'))),
            array('d' => array('due', 'bis'), 't' => 'tre', 'c' => array('c' => 'cinque', 'c2' => 'cinco'), 'q' => 'quattro'),
            '::arrayDeepMerge() recursively merges arrays preserving literal keys'
        );
        $this->is(
            sfToolkit::arrayDeepMerge(array(2 => 'due', 3 => 'tre'), array(2 => 'bis', 4 => 'quattro')),
            array(2 => 'bis', 3 => 'tre', 4 => 'quattro'),
            '::arrayDeepMerge() merges linear arrays preserving numerical keys'
        );
        $this->is(
            sfToolkit::arrayDeepMerge(array(2 => array('due'), 3 => 'tre'), array(2 => array('bis', 'bes'), 4 => 'quattro')),
            array(2 => array('bis', 'bes'), 3 => 'tre', 4 => 'quattro'),
            '::arrayDeepMerge() recursively merges arrays preserving numerical keys'
        );

        $arr = array(
            'foobar' => 'foo',
            'foo' => array(
                'bar' => array(
                    'baz' => 'foo bar',
                ),
            ),
            'bar' => array(
                'foo',
                'bar',
            ),
            'simple' => 'string',
        );

        // ::getArrayValueForPath()
        $this->diag('::getArrayValueForPath()');

        $this->is(sfToolkit::getArrayValueForPath($arr, 'foobar'), 'foo', '::getArrayValueForPath() returns the value of the path if it exists');
        $this->is(sfToolkit::getArrayValueForPath($arr, 'barfoo'), null, '::getArrayValueForPath() returns null if the path does not exist');
        $this->is(sfToolkit::getArrayValueForPath($arr, 'barfoo', 'bar'), 'bar', '::getArrayValueForPath() takes a default value as its third argument');

        $this->is(sfToolkit::getArrayValueForPath($arr, 'foo[bar][baz]'), 'foo bar', '::getArrayValueForPath() works with deep paths');
        $this->is(sfToolkit::getArrayValueForPath($arr, 'foo[bar][bar]'), null, '::getArrayValueForPath() works with deep paths');
        $this->is(sfToolkit::getArrayValueForPath($arr, 'foo[bar][bar]', 'bar'), 'bar', '::getArrayValueForPath() works with deep paths');

        $this->is(sfToolkit::getArrayValueForPath($arr, 'foo[]'), array('bar' => array('baz' => 'foo bar')), '::getArrayValueForPath() accepts a [] at the end to check for an array');
        $this->is(sfToolkit::getArrayValueForPath($arr, 'foobar[]'), null, '::getArrayValueForPath() accepts a [] at the end to check for an array');
        $this->is(sfToolkit::getArrayValueForPath($arr, 'barfoo[]'), null, '::getArrayValueForPath() accepts a [] at the end to check for an array');
        $this->is(sfToolkit::getArrayValueForPath($arr, 'foobar[]', 'foo'), 'foo', '::getArrayValueForPath() accepts a [] at the end to check for an array');

        $this->is(sfToolkit::getArrayValueForPath($arr, 'bar[1]'), 'bar', '::getArrayValueForPath() can take an array indexed by integer');
        $this->is(sfToolkit::getArrayValueForPath($arr, 'bar[2]'), null, '::getArrayValueForPath() can take an array indexed by integer');
        $this->is(sfToolkit::getArrayValueForPath($arr, 'bar[2]', 'foo'), 'foo', '::getArrayValueForPath() can take an array indexed by integer');

        $this->is(sfToolkit::getArrayValueForPath($arr, 'foo[bar][baz][booze]'), null, '::getArrayValueForPath() is not fooled by php mistaking strings and array');

        // ::addIncludePath()
        $this->diag('::addIncludePath()');
        $path = get_include_path();
        $this->is(sfToolkit::addIncludePath(__DIR__), $path, '::addIncludePath() returns the previous include_path');
        $this->is(get_include_path(), __DIR__.PATH_SEPARATOR.$path, '::addIncludePath() adds a path to the front of include_path');

        sfToolkit::addIncludePath(__DIR__, 'back');
        $this->is(get_include_path(), $path.PATH_SEPARATOR.__DIR__, '::addIncludePath() moves a path to the end of include_path');

        sfToolkit::addIncludePath(array(
            __DIR__,
            __DIR__.'/..',
        ));
        $this->is(get_include_path(), __DIR__.PATH_SEPARATOR.__DIR__.'/..'.PATH_SEPARATOR.$path, '::addIncludePath() adds multiple paths the the front of include_path');

        sfToolkit::addIncludePath(array(
            __DIR__,
            __DIR__.'/..',
        ), 'back');
        $this->is(get_include_path(), $path.PATH_SEPARATOR.__DIR__.PATH_SEPARATOR.__DIR__.'/..', '::addIncludePath() adds multiple paths the the back of include_path');

        try {
            sfToolkit::addIncludePath(__DIR__, 'foobar');
            $this->fail('::addIncludePath() throws an exception if position is not valid');
        } catch (Exception $e) {
            $this->pass('::addIncludePath() throws an exception if position is not valid');
        }
    }
}
