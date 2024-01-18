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
class sfValidatorUrlTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $v = new sfValidatorUrl();

        // ->clean()
        $this->diag('->clean()');
        foreach (array(
            'http://www.google.com',
            'https://google.com/',
            'https://google.com:80/',
            'http://www.symfony-project.com/',
            'http://127.0.0.1/',
            'http://127.0.0.1:80/',
            'ftp://google.com/foo.tgz',
            'ftps://google.com/foo.tgz',
        ) as $url) {
            $this->is($v->clean($url), $url, '->clean() checks that the value is a valid URL');
        }

        foreach (array(
            'google.com',
            'http:/google.com',
            'http://google.com::aa',
        ) as $nonUrl) {
            try {
                $v->clean($nonUrl);
                $this->fail('->clean() throws an sfValidatorError if the value is not a valid URL');
                $this->skip('', 1);
            } catch (sfValidatorError $e) {
                $this->pass('->clean() throws an sfValidatorError if the value is not a valid URL');
                $this->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
            }
        }

        $v = new sfValidatorUrl(array('protocols' => array('http', 'https')));
        try {
            $v->clean('ftp://google.com/foo.tgz');
            $this->fail('->clean() only allows protocols specified in the protocols option');
        } catch (sfValidatorError $e) {
            $this->pass('->clean() only allows protocols specified in the protocols option');
        }
    }
}
