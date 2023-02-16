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
require_once __DIR__.'/../fixtures/myController3.php';
require_once __DIR__.'/../fixtures/myRequest2.php';
require_once __DIR__.'/../fixtures/testObject.php';
require_once __DIR__.'/../fixtures/testObjectWithToString.php';
require_once __DIR__.'/../fixtures/BaseForm.php';
require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../../lib/helper/AssetHelper.php';
require_once __DIR__.'/../../lib/helper/UrlHelper.php';
require_once __DIR__.'/../../lib/helper/TagHelper.php';

/**
 * @internal
 *
 * @coversNothing
 */
class UrlHelperTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        sfForm::enableCSRFProtection();
        $context = sfContext::getInstance(array('controller' => 'myController3', 'request' => 'myRequest2'), true);

        // url_for()
        $this->diag('url_for()');
        $this->is(url_for('@test'), 'module/action', 'url_for() converts an internal URI to a web URI');
        $this->is(url_for('@test', true), '/module/action', 'url_for() can take an absolute boolean as its second argument');
        $this->is(url_for('@test', false), 'module/action', 'url_for() can take an absolute boolean as its second argument');

        // link_to()
        $this->diag('link_to()');
        $this->is(link_to('test', '@homepage'), '<a href="module/action">test</a>', 'link_to() returns an HTML "a" tag');
        $this->is(link_to('test', '@homepage', array('absolute' => true)), '<a href="/module/action">test</a>', 'link_to() can take an "absolute" option');
        $this->is(link_to('test', '@homepage', array('absolute' => false)), '<a href="module/action">test</a>', 'link_to() can take an "absolute" option');
        $this->is(link_to('test', '@homepage', array('query_string' => 'foo=bar')), '<a href="module/action?foo=bar">test</a>', 'link_to() can take a "query_string" option');
        $this->is(link_to('test', '@homepage', array('anchor' => 'bar')), '<a href="module/action#bar">test</a>', 'link_to() can take an "anchor" option');
        $this->is(link_to('', '@homepage'), '<a href="module/action">module/action</a>', 'link_to() takes the url as the link name if the first argument is empty');
        $this->like(link_to('test', '@homepage', array('method' => 'post')), '/==TOKEN==/', 'link_to() includes CSRF token from BaseForm');

        // button_to()
        $this->diag('button_to()');
        $this->is(button_to('test', '@homepage'), '<input value="test" type="button" onclick="document.location.href=\'module/action\';" />', 'button_to() returns an HTML "input" tag');
        $this->is(button_to('test', '@homepage', array('query_string' => 'foo=bar')), '<input value="test" type="button" onclick="document.location.href=\'module/action?foo=bar\';" />', 'button_to() returns an HTML "input" tag');
        $this->is(button_to('test', '@homepage', array('anchor' => 'bar')), '<input value="test" type="button" onclick="document.location.href=\'module/action#bar\';" />', 'button_to() returns an HTML "input" tag');
        $this->is(button_to('test', '@homepage', array('popup' => 'true', 'query_string' => 'foo=bar')), '<input value="test" type="button" onclick="var w=window.open(\'module/action?foo=bar\');w.focus();return false;" />', 'button_to() returns an HTML "input" tag');
        $this->is(button_to('test', '@homepage', 'popup=true'), '<input value="test" type="button" onclick="var w=window.open(\'module/action\');w.focus();return false;" />', 'button_to() accepts options as string');
        $this->is(button_to('test', '@homepage', 'confirm=really?'), '<input value="test" type="button" onclick="if (confirm(\'really?\')) { return document.location.href=\'module/action\';} else return false;" />', 'button_to() works with confirm option');
        $this->is(button_to('test', '@homepage', 'popup=true confirm=really?'), '<input value="test" type="button" onclick="if (confirm(\'really?\')) { var w=window.open(\'module/action\');w.focus(); };return false;" />', 'button_to() works with confirm and popup option');
        $this->like(button_to('test', '@homepage', array('method' => 'post')), '/==TOKEN==/', 'button_to() includes CSRF token from BaseForm');

        try {
            $o1 = new testObject();
            link_to($o1, '@homepage');
            $this->fail('link_to() can take an object as its first argument if __toString() method is defined');
        } catch (sfException $e) {
            $this->pass('link_to() can take an object as its first argument if __toString() method is defined');
        }

        $o2 = new testObjectWithToString();
        $this->is(link_to($o2, '@homepage'), '<a href="module/action">test</a>', 'link_to() can take an object as its first argument');

        // link_to_if()
        $this->diag('link_to_if()');
        $this->is(link_to_if(true, 'test', '@homepage'), '<a href="module/action">test</a>', 'link_to_if() returns an HTML "a" tag if the condition is true');
        $this->is(link_to_if(false, 'test', '@homepage'), '<span>test</span>', 'link_to_if() returns an HTML "span" tag by default if the condition is false');
        $this->is(link_to_if(false, 'test', '@homepage', array('tag' => 'div')), '<div>test</div>', 'link_to_if() takes a "tag" option');
        $this->is(link_to_if(true, 'test', '@homepage', 'tag=div'), '<a href="module/action">test</a>', 'link_to_if() removes "tag" option (given as string) in true case');
        $this->is(link_to_if(true, 'test', '@homepage', array('tag' => 'div')), '<a href="module/action">test</a>', 'link_to_if() removes "tag" option (given as array) in true case');
        $this->is(link_to_if(false, 'test', '@homepage', array('query_string' => 'foo=bar', 'absolute' => true, 'absolute_url' => 'http://www.google.com/')), '<span>test</span>', 'link_to_if() returns an HTML "span" tag by default if the condition is false');
        $this->is(link_to_if(true, 'test', 'homepage', array(), array('class' => 'test')), '<a class="test" href="homepage">test</a>', 'link_to_if() accepts link_to2 compatible usage');
        $this->is(link_to_if(false, 'test', 'homepage', array(), array('class' => 'test')), '<span class="test">test</span>', 'link_to_if() accepts link_to2 compatible usage');

        // link_to_unless()
        $this->diag('link_to_unless()');
        $this->is(link_to_unless(false, 'test', '@homepage'), '<a href="module/action">test</a>', 'link_to_unless() returns an HTML "a" tag if the condition is false');
        $this->is(link_to_unless(true, 'test', '@homepage'), '<span>test</span>', 'link_to_unless() returns an HTML "span" tag by default if the condition is true');
        $this->is(link_to_unless(true, 'test', 'homepage', array(), array('class' => 'test')), '<span class="test">test</span>', 'link_to_unless() accepts link_to2 compatible usage');
        $this->is(link_to_unless(false, 'test', 'homepage', array(), array('class' => 'test')), '<a class="test" href="homepage">test</a>', 'link_to_unless() accepts link_to2 compatible usage');

        // public_path()
        $this->diag('public_path()');
        $this->is(public_path('pdf/download.pdf'), '/public/pdf/download.pdf', 'public_path() returns the public path');
        $this->is(public_path('/pdf/download.pdf'), '/public/pdf/download.pdf', 'public_path() returns the public path if starting with slash');
        $this->is(public_path('pdf/download.pdf', true), 'https://example.org/public/pdf/download.pdf', 'public_path() returns the public path');

        // mail_to()
        $this->diag('mail_to()');
        $this->is(mail_to('fabien.potencier@symfony-project.com'), '<a href="mailto:fabien.potencier@symfony-project.com">fabien.potencier@symfony-project.com</a>', 'mail_to() creates a mailto a tag');
        $this->is(mail_to('fabien.potencier@symfony-project.com', 'fabien'), '<a href="mailto:fabien.potencier@symfony-project.com">fabien</a>', 'mail_to() creates a mailto a tag');
        preg_match('/href="(.+?)"/', mail_to('fabien.potencier@symfony-project.com', 'fabien', array('encode' => true)), $matches);
        $this->is(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'), 'mailto:fabien.potencier@symfony-project.com', 'mail_to() can encode the email address');

        $this->diag('mail_to test');
        $this->is(mail_to('webmaster@example.com'), '<a href="mailto:webmaster@example.com">webmaster@example.com</a>', 'mail_to with only given email works');
        $this->is(mail_to('webmaster@example.com', 'send us an email'), '<a href="mailto:webmaster@example.com">send us an email</a>', 'mail_to with given email and title works');
        $this->isnt(mail_to('webmaster@example.com', 'encoded', array('encode' => true)), '<a href="mailto:webmaster@example.com">encoded</a>', 'mail_to with encoding works');

        $this->is(mail_to('webmaster@example.com', '', array(), array('subject' => 'test subject', 'body' => 'test body')), '<a href="mailto:webmaster@example.com?subject=test+subject&amp;body=test+body">webmaster@example.com</a>', 'mail_to() works with given default values in array form');
        $this->is(mail_to('webmaster@example.com', '', array(), 'subject=test subject body=test body'), '<a href="mailto:webmaster@example.com?subject=test+subject&amp;body=test+body">webmaster@example.com</a>', 'mail_to() works with given default values in string form');
        $this->is(mail_to('webmaster@example.com', '', array(), 'subject=Hello World and more'), '<a href="mailto:webmaster@example.com?subject=Hello+World+and+more">webmaster@example.com</a>', 'mail_to() works with given default value with spaces');
    }
}
