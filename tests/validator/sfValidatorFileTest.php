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
require_once __DIR__.'/../fixtures/testValidatorFile.php';
require_once __DIR__.'/../fixtures/myValidatedFile.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfValidatorFileTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $tmpDir = sys_get_temp_dir();
        $content = 'This is an ASCII file.';
        file_put_contents($tmpDir.'/test.txt', $content);

        // ->getMimeTypesFromCategory()
        $this->diag('->getMimeTypesFromCategory()');
        $v = new testValidatorFile();
        try {
            $this->is($v->getMimeTypesFromCategory('non_existant_category'), '');
            $this->fail('->getMimeTypesFromCategory() throws an InvalidArgumentException if the category does not exist');
        } catch (InvalidArgumentException $e) {
            $this->pass('->getMimeTypesFromCategory() throws an InvalidArgumentException if the category does not exist');
        }
        $categories = $v->getOption('mime_categories');
        $this->is($v->getMimeTypesFromCategory('web_images'), $categories['web_images'], '->getMimeTypesFromCategory() returns an array of mime types for a given category');
        $v->setOption('mime_categories', array_merge($v->getOption('mime_categories'), array('text' => array('text/plain'))));
        $this->is($v->getMimeTypesFromCategory('text'), array('text/plain'), '->getMimeTypesFromCategory() returns an array of mime types for a given category');

        // ->guessFromFileinfo()
        $this->diag('->guessFromFileinfo()');
        if (!function_exists('finfo_open')) {
            $this->skip('finfo_open is not available', 2);
        } else {
            $v = new testValidatorFile();
            $this->is($v->guessFromFileinfo($tmpDir.'/test.txt'), 'text/plain', '->guessFromFileinfo() guesses the type of a given file');
            $this->is($v->guessFromFileinfo($tmpDir.'/foo.txt'), null, '->guessFromFileinfo() returns null if the file type is not guessable');
        }

        // ->guessFromMimeContentType()
        $this->diag('->guessFromMimeContentType()');
        if (!function_exists('mime_content_type')) {
            $this->skip('mime_content_type is not available', 2);
        } else {
            $v = new testValidatorFile();
            $mimeType = $v->guessFromMimeContentType($tmpDir.'/test.txt');
            if (version_compare(PHP_VERSION, '5.3', '<') && false === $mimeType) {
                $this->skip('mime_content_type has some issue with php 5.2', 1);
            } else {
                $this->is($mimeType, 'text/plain', '->guessFromMimeContentType() guesses the type of a given file');
            }
            $this->is($v->guessFromMimeContentType($tmpDir.'/foo.txt'), null, '->guessFromMimeContentType() returns null if the file type is not guessable');
        }

        // ->guessFromFileBinary()
        $this->diag('->guessFromFileBinary()');
        $v = new testValidatorFile();
        $this->is($v->guessFromFileBinary($tmpDir.'/test.txt'), 'text/plain', '->guessFromFileBinary() guesses the type of a given file');
        $this->is($v->guessFromFileBinary($tmpDir.'/foo.txt'), null, '->guessFromFileBinary() returns null if the file type is not guessable');
        $this->like($v->guessFromFileBinary('/bin/ls'), (PHP_OS != 'Darwin') ? '/^application\/x-(pie-executable|executable|sharedlib)$/' : '/^application\/(octet-stream|x-mach-binary)$/', '->guessFromFileBinary() returns correct type if file is guessable');
        $this->is($v->guessFromFileBinary('-test'), null, '->guessFromFileBinary() returns null if file path has leading dash');

        // ->getMimeType()
        $this->diag('->getMimeType()');
        $v = new testValidatorFile();
        $this->is($v->getMimeType($tmpDir.'/test.txt', 'image/png'), 'text/plain', '->getMimeType() guesses the type of a given file');
        $this->is($v->getMimeType($tmpDir.'/foo.txt', 'text/plain'), 'text/plain', '->getMimeType() returns the default type if the file type is not guessable');

        $v->setOption('mime_type_guessers', array_merge(array(array($v, 'guessFromNothing')), $v->getOption('mime_type_guessers')));
        $this->is($v->getMimeType($tmpDir.'/test.txt', 'image/png'), 'nothing/plain', '->getMimeType() takes all guessers from the mime_type_guessers option');

        // ->clean()
        $this->diag('->clean()');
        $v = new testValidatorFile();
        try {
            $v->clean(array('test' => true));
            $this->fail('->clean() throws an sfValidatorError if the given value is not well formatted');
            $this->skip('', 1);
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the given value is not well formatted');
            $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
        }
        $f = $v->clean(array('tmp_name' => $tmpDir.'/test.txt'));
        $this->ok($f instanceof sfValidatedFile, '->clean() returns a sfValidatedFile instance');
        $this->is($f->getOriginalName(), '', '->clean() returns a sfValidatedFile with an empty original name if the name is not passed in the initial value');
        $this->is($f->getSize(), strlen($content), '->clean() returns a sfValidatedFile with a computed file size if the size is not passed in the initial value');
        $this->is($f->getType(), 'text/plain', '->clean() returns a sfValidatedFile with a guessed content type');

        $v->setOption('validated_file_class', 'myValidatedFile');
        $f = $v->clean(array('tmp_name' => $tmpDir.'/test.txt'));
        $this->ok($f instanceof myValidatedFile, '->clean() can take a "validated_file_class" option');

        foreach (array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE, UPLOAD_ERR_PARTIAL, UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION) as $error) {
            try {
                $v->clean(array('tmp_name' => $tmpDir.'/test.txt', 'error' => $error));
                $this->fail('->clean() throws an sfValidatorError if the error code is not UPLOAD_ERR_OK (0)');
                $this->skip('', 1);
            } catch (sfValidatorError $e) {
                $this->pass('->clean() throws an sfValidatorError if the error code is not UPLOAD_ERR_OK (0)');
                $this->is($e->getCode(), $code = strtolower(str_replace('UPLOAD_ERR_', '', $e->getCode())), '->clean() throws an error code of '.$code);
            }
        }

        // max file size
        $this->diag('max file size');
        $v->setOption('max_size', 4);
        try {
            $v->clean(array('tmp_name' => $tmpDir.'/test.txt'));
            $this->skip();
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the file size is too large');
            $this->is($e->getCode(), 'max_size', '->clean() throws an error code of max_size');
        }
        $v->setOption('max_size', null);

        // mime types
        $this->diag('mime types');
        $v->setOption('mime_types', 'web_images');
        try {
            $v->clean(array('tmp_name' => $tmpDir.'/test.txt'));
            $this->skip();
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the file mime type is not in mime_types option');
            $this->is($e->getCode(), 'mime_types', '->clean() throws an error code of mime_types');
        }
        $v->setOption('mime_types', null);

        // required
        $v = new testValidatorFile();
        try {
            $v->clean(array('tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'name' => '', 'size' => 0, 'type' => ''));
            $this->fail('->clean() throws an sfValidatorError if the file is required and no file is uploaded');
            $this->skip();
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the file is required and no file is uploaded');
            $this->is($e->getCode(), 'required', '->clean() throws an error code of required');
        }
        try {
            $v->clean(null);
            $this->fail('->clean() throws an sfValidatorError if the file is required and no file is uploaded');
            $this->skip();
        } catch (sfValidatorError $e) {
            $this->pass('->clean() throws an sfValidatorError if the file is required and no file is uploaded');
            $this->is($e->getCode(), 'required', '->clean() throws an error code of required');
        }
        $v = new testValidatorFile(array('required' => false));
        $this->is($v->clean(array('tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'name' => '', 'size' => 0, 'type' => '')), null, '->clean() handles the required option correctly');

        // sfValidatedFile

        // ->getOriginalName() ->getTempName() ->getSize() ->getType()
        $this->diag('->getOriginalName() ->getTempName() ->getSize() ->getType()');
        sfToolkit::clearDirectory($tmpDir.'/foo');
        if (is_dir($tmpDir.'/foo')) {
            rmdir($tmpDir.'/foo');
        }
        $f = new sfValidatedFile('test.txt', 'text/plain', $tmpDir.'/test.txt', strlen($content));
        $this->is($f->getOriginalName(), 'test.txt', '->getOriginalName() returns the original name');
        $this->is($f->getTempName(), $tmpDir.'/test.txt', '->getTempName() returns the temp name');
        $this->is($f->getType(), 'text/plain', '->getType() returns the content type');
        $this->is($f->getSize(), strlen($content), '->getSize() returns the size of the uploaded file');

        // ->save() ->isSaved() ->getSavedName()
        $this->diag('->save() ->isSaved() ->getSavedName()');
        $f = new sfValidatedFile('test.txt', 'text/plain', $tmpDir.'/test.txt', strlen($content));
        $this->is($f->isSaved(), false, '->isSaved() returns false if the file has not been saved');
        $this->is($f->getSavedName(), null, '->getSavedName() returns null if the file has not been saved');
        $filename = $f->save($tmpDir.'/foo/test1.txt');
        $this->is($filename, $tmpDir.'/foo/test1.txt', '->save() returns the saved filename');
        $this->is(file_get_contents($tmpDir.'/foo/test1.txt'), file_get_contents($tmpDir.'/test.txt'), '->save() saves the file to the given path');
        $this->is($f->isSaved(), true, '->isSaved() returns true if the file has been saved');
        $this->is($f->getSavedName(), $tmpDir.'/foo/test1.txt', '->getSavedName() returns the saved file name');

        $f = new sfValidatedFile('test.txt', 'text/plain', $tmpDir.'/test.txt', strlen($content), $tmpDir);
        $filename = $f->save($tmpDir.'/foo/test1.txt');
        $this->is($filename, 'foo/test1.txt', '->save() returns the saved filename relative to the path given');
        $this->is(file_get_contents($tmpDir.'/foo/test1.txt'), file_get_contents($tmpDir.'/test.txt'), '->save() saves the file to the given path');
        $this->is($f->getSavedName(), $tmpDir.'/foo/test1.txt', '->getSavedName() returns the saved file name');

        $filename = $f->save('foo/test1.txt');
        $this->is($filename, 'foo/test1.txt', '->save() returns the saved filename relative to the path given');
        $this->is(file_get_contents($tmpDir.'/foo/test1.txt'), file_get_contents($tmpDir.'/test.txt'), '->save() saves the file to the given path and uses the path if the file is not absolute');
        $this->is($f->getSavedName(), $tmpDir.'/foo/test1.txt', '->getSavedName() returns the saved file name');

        $filename = $f->save();
        $this->is(file_get_contents($tmpDir.'/'.$filename), file_get_contents($tmpDir.'/test.txt'), '->save() returns the generated file name is none was given');
        $this->is($f->getSavedName(), $tmpDir.'/'.$filename, '->getSavedName() returns the saved file name');

        try {
            $f = new sfValidatedFile('test.txt', 'text/plain', $tmpDir.'/test.txt', strlen($content));
            $f->save();
            $this->fail('->save() throws an Exception if you don\'t give a filename and the path is empty');
        } catch (Exception $e) {
            $this->pass('->save() throws an Exception if you don\'t give a filename and the path is empty');
        }

        try {
            $f->save($tmpDir.'/test.txt/test1.txt');
            $this->fail('->save() throws an Exception if the directory already exists and is not a directory');
        } catch (Exception $e) {
            $this->pass('->save() throws an Exception if the directory already exists and is not a directory');
        }

        // ->getExtension()
        $this->diag('->getExtension()');
        $f = new sfValidatedFile('test.txt', 'text/plain', $tmpDir.'/test.txt', strlen($content));
        $this->is($f->getExtension(), '.txt', '->getExtension() returns file extension based on the content type');
        $f = new sfValidatedFile('test.txt', 'image/x-png', $tmpDir.'/test.txt', strlen($content));
        $this->is($f->getExtension(), '.png', '->getExtension() returns file extension based on the content type');
        $f = new sfValidatedFile('test.txt', 'very/specific', $tmpDir.'/test.txt', strlen($content));
        $this->is($f->getExtension(), '', '->getExtension() returns an empty string if it does not know the content type');
        $f = new sfValidatedFile('test.txt', '', $tmpDir.'/test.txt', strlen($content));
        $this->is($f->getExtension(), '', '->getExtension() returns an empty string if the content type is empty');
        $this->is($f->getExtension('bin'), 'bin', '->getExtension() takes a default extension as its first argument');

        // ->getOriginalExtension()
        $this->diag('->getOriginalExtension()');
        $f = new sfValidatedFile('test.txt', 'text/plain', $tmpDir.'/test.txt', strlen($content));
        $this->is($f->getOriginalExtension(), '.txt', '->getOriginalExtension() returns the extension based on the uploaded file name');
        $f = new sfValidatedFile('test', 'text/plain', $tmpDir.'/test.txt', strlen($content));
        $this->is($f->getOriginalExtension(), '', '->getOriginalExtension() returns an empty extension if the uploaded file name has no extension');
        $this->is($f->getOriginalExtension('bin'), 'bin', '->getOriginalExtension() takes a default extension as its first argument');

        unlink($tmpDir.'/test.txt');
        sfToolkit::clearDirectory($tmpDir.'/foo');
        rmdir($tmpDir.'/foo');
    }
}
