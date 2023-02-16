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
class sfValidatorFileMultiTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $tmpDir = sys_get_temp_dir();
        $content = 'This is an ASCII file.';
        $content2 = 'This is an ASCII file. And another one.';
        file_put_contents($tmpDir.'/test.txt', $content);
        file_put_contents($tmpDir.'/test2.txt', $content2);

        // ->clean()
        $this->diag('->clean()');
        $v = new sfValidatorFileMulti();

        $f = $v->clean(array(
            array('tmp_name' => $tmpDir.'/test.txt'),
            array('tmp_name' => $tmpDir.'/test2.txt'),
        ));

        $this->ok(is_array($f), '->clean() returns an array of sfValidatedFile instances');

        $this->ok($f[0] instanceof sfValidatedFile, '->clean() returns an array of sfValidatedFile');
        $this->is($f[0]->getOriginalName(), '', '->clean() returns an array of sfValidatedFile with an empty original name if the name is not passed in the initial value');
        $this->is($f[0]->getSize(), strlen($content), '->clean() returns an array of sfValidatedFile with a computed file size if the size is not passed in the initial value');
        $this->is($f[0]->getType(), 'text/plain', '->clean() returns an array of sfValidatedFile with a guessed content type');

        $this->ok($f[1] instanceof sfValidatedFile, '->clean() returns an array of sfValidatedFile');
        $this->is($f[1]->getOriginalName(), '', '->clean() returns an array of sfValidatedFile with an empty original name if the name is not passed in the initial value');
        $this->is($f[1]->getSize(), strlen($content2), '->clean() returns an array of sfValidatedFile with a computed file size if the size is not passed in the initial value');
        $this->is($f[1]->getType(), 'text/plain', '->clean() returns an array of sfValidatedFile with a guessed content type');

        unlink($tmpDir.'/test.txt');
        unlink($tmpDir.'/test2.txt');
        sfToolkit::clearDirectory($tmpDir.'/foo');
    }
}
