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
require_once __DIR__.'/../../lib/helper/TagHelper.php';
require_once __DIR__.'/../../lib/helper/TextHelper.php';

/**
 * @internal
 *
 * @coversNothing
 */
class TextHelperTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // truncate_text()
        $this->diag('truncate_text()');
        $this->is(truncate_text(''), '', 'truncate_text() does nothing on an empty string');

        $this->is(truncate_text('Test'), 'Test', 'truncate_text() truncates to 30 characters by default');

        $text = str_repeat('A', 35);
        $truncated = str_repeat('A', 27).'...';
        $this->is(truncate_text($text), $truncated, 'truncate_text() adds ... to truncated text');

        $text = str_repeat('A', 35);
        $truncated = str_repeat('A', 22).'...';
        $this->is(truncate_text($text, 25), $truncated, 'truncate_text() takes the max length as its second argument');

        $text = str_repeat('A', 35);
        $truncated = str_repeat('A', 21).'BBBB';
        $this->is(truncate_text($text, 25, 'BBBB'), $truncated, 'truncate_text() takes the ... text as its third argument');

        $text = str_repeat('A', 10).str_repeat(' ', 10).str_repeat('A', 10);
        $truncated_true = str_repeat('A', 10).'...';
        $truncated_false = str_repeat('A', 10).str_repeat(' ', 2).'...';
        $this->is(truncate_text($text, 15, '...', false), $truncated_false, 'truncate_text() accepts a truncate lastspace boolean as its fourth argument');
        $this->is(truncate_text($text, 15, '...', true), $truncated_true, 'truncate_text() accepts a truncate lastspace boolean as its fourth argument');

        if (extension_loaded('mbstring')) {
            $oldEncoding = mb_internal_encoding();
            $this->is(truncate_text('のビヘイビアにパラメーターを渡すことで特定のモデルでのフォーム生成を無効にできます', 11), 'のビヘイビアにパ...', 'truncate_text() handles unicode characters using mbstring if available');
            $this->is(mb_internal_encoding(), $oldEncoding, 'truncate_text() sets back the internal encoding in case it changes it');
        } else {
            $this->skip('mbstring extension is not enabled', 2);
        }

        $text = 'Web applications spend a large share of their code transforming arrays of data. PHP is a wonderful language for that, because it offers a lot of array manipulation functions. But web developers are actually required to translate business logic into a program - not to mess up with arrays. In fact, web developers should spend the least possible amount of tim';
        $result = 'Web applications spend a large share of their code transforming arrays of data. PHP is a wonderful language for that, because it offers a lot of array manipulation functions. But web developers are actually required to translate business logic into a program - not to mess up with arrays. [...]';

        $this->is(truncate_text($text, 200, '[...]', false, '/[.]\s+?/', 0), $result, 'truncate_text() truncate text after the first found pattern after 200 characters');

        $result = 'Web applications spend a large share of their code transforming arrays of data. [...]';
        $this->is(truncate_text($text, 200, '[...]', false, '/[.]\s+?/'), $result, 'truncate_text() truncate text after the last found pattern before 200 characters');

        $text = 'Web applications spend a large share of their code transforming arrays of data. PHP is a wonderful language for that.';
        $this->is(truncate_text($text, 200, '[...]', false, '/[.]\s+?/'), $text, 'truncate_text() does nothing for a text that not exceed 200 characters');

        $text = 'Web applications spend a large share of their code transforming arrays of data PHP is a wonderful language for that, because it offers a lot of array manipulation functions But web developers are actually required to translate business logic into a program - not to mess up with arrays In fact, web developers should spend the least possible amount of tim';
        $result = 'Web applications spend a large share of their code transforming arrays of data PHP is a wonderful language for that, because it offers a lot of array manipulation functions But web developers are[...]';
        $this->is(truncate_text($text, 200, '[...]', false, '/[.]\s+?/', 0), $result, 'truncate_text() whitout truncate_pattern on text, truncate it just after 200 characters');

        // highlight_text()
        $this->diag('highlight_text()');
        $this->is(highlight_text('This is a beautiful morning', 'beautiful'),
            'This is a <strong class="highlight">beautiful</strong> morning',
            'text_highlighter() highlights a word given as its second argument'
        );

        $this->is(highlight_text('This is a beautiful morning, but also a beautiful day', 'beautiful'),
            'This is a <strong class="highlight">beautiful</strong> morning, but also a <strong class="highlight">beautiful</strong> day',
            'text_highlighter() highlights all occurrences of a word given as its second argument'
        );

        $this->is(highlight_text('This is a beautiful morning, but also a beautiful day', 'beautiful', '<b>\\1</b>'),
            'This is a <b>beautiful</b> morning, but also a <b>beautiful</b> day',
            'text_highlighter() takes a pattern as its third argument'
        );

        $this->is(highlight_text('', 'beautiful'), '', 'text_highlighter() returns an empty string if input is empty');
        $this->is(highlight_text('', ''), '', 'text_highlighter() returns an empty string if input is empty');
        $this->is(highlight_text('foobar', 'beautiful'), 'foobar', 'text_highlighter() does nothing is string to highlight is not present');
        $this->is(highlight_text('foobar', ''), 'foobar', 'text_highlighter() returns input if string to highlight is not present');

        $this->is(highlight_text('This is a beautiful! morning', 'beautiful!'), 'This is a <strong class="highlight">beautiful!</strong> morning', 'text_highlighter() escapes search string to be safe in a regex');
        $this->is(highlight_text('This is a beautiful! morning', 'beautiful! morning'), 'This is a <strong class="highlight">beautiful! morning</strong>', 'text_highlighter() escapes search string to be safe in a regex');
        $this->is(highlight_text('This is a beautiful? morning', 'beautiful? morning'), 'This is a <strong class="highlight">beautiful? morning</strong>', 'text_highlighter() escapes search string to be safe in a regex');

        $this->is(highlight_text('The http://www.google.com/ website is great', 'http://www.google.com/'), 'The <strong class="highlight">http://www.google.com/</strong> website is great', 'text_highlighter() escapes search string to be safe in a regex');

        // excerpt_text()
        $this->diag('excerpt_text()');
        $this->is(excerpt_text('', 'foo', 5), '', 'text_excerpt() return an empty string if argument is empty');
        $this->is(excerpt_text('foo', '', 5), '', 'text_excerpt() return an empty string if phrase is empty');
        $this->is(excerpt_text('This is a beautiful morning', 'beautiful', 5), '...is a beautiful morn...', 'text_excerpt() creates an excerpt of a text');
        $this->is(excerpt_text('This is a beautiful morning', 'this', 5), 'This is a...', 'text_excerpt() creates an excerpt of a text');
        $this->is(excerpt_text('This is a beautiful morning', 'morning', 5), '...iful morning', 'text_excerpt() creates an excerpt of a text');
        $this->is(excerpt_text('This is a beautiful morning', 'morning', 5, '...', true), '... morning', 'text_excerpt() takes a fifth argument allowing excerpt on whitespace');
        $this->is(excerpt_text('This is a beautiful morning', 'beautiful', 5, '...', true), '... a beautiful ...', 'text_excerpt() takes a fifth argument allowing excerpt on whitespace');
        $this->is(excerpt_text('This is a beautiful morning', 'This', 5, '...', true), 'This is ...', 'text_excerpt() takes a fifth argument allowing excerpt on whitespace');
        $this->is(excerpt_text('This is a beautiful morning', 'day'), '', 'text_excerpt() does nothing if the search string is not in input');

        // wrap_text()
        $this->diag('wrap_text()');
        $line = 'This is a very long line to be wrapped...';
        $this->is(wrap_text($line), "This is a very long line to be wrapped...\n", 'wrap_text() wraps long lines with a default of 80');
        $this->is(wrap_text($line, 10), "This is a\nvery long\nline to be\nwrapped...\n", 'wrap_text() takes a line length as its second argument');
        $this->is(wrap_text($line, 5), "This\nis a\nvery\nlong\nline\nto be\nwrapped...\n", 'wrap_text() takes a line length as its second argument');

        // simple_format_text()
        $this->diag('simple_format_text()');
        $this->is(simple_format_text("crazy\r\n cross\r platform linebreaks"), "<p>crazy\n<br /> cross\n<br /> platform linebreaks</p>", 'text_simple_format() replaces \n by <br />');
        $this->is(simple_format_text("A paragraph\n\nand another one!"), '<p>A paragraph</p><p>and another one!</p>', 'text_simple_format() replaces \n\n by <p>');
        $this->is(simple_format_text("A paragraph\n\n\n\nand another one!"), '<p>A paragraph</p><p>and another one!</p>', 'text_simple_format() replaces \n\n\n\n by <p>');
        $this->is(simple_format_text("A paragraph\n With a newline"), "<p>A paragraph\n<br /> With a newline</p>", 'text_simple_format() wrap all string with <p>');
        $this->is(simple_format_text("1\n2\n3"), "<p>1\n<br />2\n<br />3</p>", 'text_simple_format() Ticket #6824');

        // text_strip_links()
        $this->diag('text_strip_links()');
        $this->is(strip_links_text("<a href='almost'>on my mind</a>"), 'on my mind', 'text_strip_links() strips all links in input');
        $this->is(strip_links_text('<a href="first.html">first</a> and <a href="second.html">second</a>'), 'first and second', 'text_strip_links() strips all links in input');

        // auto_link_text()
        $this->diag('auto_link_text()');
        $email_raw = 'fabien.potencier@symfony-project.com';
        $email_result = '<a href="mailto:'.$email_raw.'">'.$email_raw.'</a>';
        $link_raw = 'http://www.google.com';
        $link_result = '<a href="'.$link_raw.'">'.$link_raw.'</a>';
        $link2_raw = 'www.google.com';
        $link2_result = '<a href="http://'.$link2_raw.'">'.$link2_raw.'</a>';

        $this->is(auto_link_text('hello '.$email_raw, 'email_addresses'), 'hello '.$email_result, 'auto_link_text() converts emails to links');
        $this->is(auto_link_text('Go to '.$link_raw, 'urls'), 'Go to '.$link_result, 'auto_link_text() converts absolute URLs to links');
        $this->is(auto_link_text('Go to '.$link_raw, 'email_addresses'), 'Go to '.$link_raw, 'auto_link_text() takes a second parameter');
        $this->is(auto_link_text('Go to '.$link_raw.' and say hello to '.$email_raw), 'Go to '.$link_result.' and say hello to '.$email_result, 'auto_link_text() converts emails and URLs if no second argument is given');
        $this->is(auto_link_text('<p>Link '.$link_raw.'</p>'), '<p>Link '.$link_result.'</p>', 'auto_link_text() converts URLs to links');
        $this->is(auto_link_text('<p>'.$link_raw.' Link</p>'), '<p>'.$link_result.' Link</p>', 'auto_link_text() converts URLs to links');
        $this->is(auto_link_text('Go to '.$link2_raw, 'urls'), 'Go to '.$link2_result, 'auto_link_text() converts URLs to links even if link does not start with http://');
        $this->is(auto_link_text('Go to '.$link2_raw, 'email_addresses'), 'Go to '.$link2_raw, 'auto_link_text() converts URLs to links');
        $this->is(auto_link_text('<p>Link '.$link2_raw.'</p>'), '<p>Link '.$link2_result.'</p>', 'auto_link_text() converts URLs to links');
        $this->is(auto_link_text('<p>'.$link2_raw.' Link</p>'), '<p>'.$link2_result.' Link</p>', 'auto_link_text() converts URLs to links');
        $this->is(auto_link_text('<p>http://www.google.com/?q=symfony Link</p>'), '<p><a href="http://www.google.com/?q=symfony">http://www.google.com/?q=symfony</a> Link</p>', 'auto_link_text() converts URLs to links');
        $this->is(auto_link_text('<p>http://www.google.com/?q=symfony+link</p>', 'all', array(), true), '<p><a href="http://www.google.com/?q=symfony+link">http://www.google.com/?q=symfony+li...</a></p>', 'auto_link_text() truncates URLs in links');
        $this->is(auto_link_text('<p>http://www.google.com/?q=symfony+link</p>', 'all', array(), true, 32, '***'), '<p><a href="http://www.google.com/?q=symfony+link">http://www.google.com/?q=symfony***</a></p>', 'auto_link_text() takes truncation parameters');
        $this->is(auto_link_text('<p>http://twitter.com/#!/fabpot</p>'), '<p><a href="http://twitter.com/#!/fabpot">http://twitter.com/#!/fabpot</a></p>', 'auto_link_text() converts URLs with complex fragments to links');
        $this->is(auto_link_text('<p>http://twitter.com/#!/fabpot is Fabien Potencier on Twitter</p>'), '<p><a href="http://twitter.com/#!/fabpot">http://twitter.com/#!/fabpot</a> is Fabien Potencier on Twitter</p>', 'auto_link_text() converts URLs with complex fragments and trailing text to links');
        $this->is(auto_link_text('hello '.$email_result, 'email_addresses'), 'hello '.$email_result, 'auto_link_text() does not double-link emails');
        $this->is(auto_link_text('<p>Link '.$link_result.'</p>'), '<p>Link '.$link_result.'</p>', 'auto_link_text() does not double-link emails');
    }
}
