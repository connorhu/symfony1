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
require_once __DIR__.'/../../lib/helper/EscapingHelper.php';

/**
 * @internal
 *
 * @coversNothing
 */
class EscapingHelperTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        sfConfig::set('sf_charset', 'UTF-8');

        // esc_entities()
        $this->diag('esc_entities()');
        $this->is(esc_entities(10), 10, 'esc_entities() does not escape integers');
        $this->is(esc_entities(false), false, 'esc_entities() does not escape booleans');
        $this->is(esc_entities('foo bar'), 'foo bar', 'esc_entities() only escapes strings');
        $this->is(esc_entities('<b>foo</b> bar'), '&lt;b&gt;foo&lt;/b&gt; bar', 'esc_entities() only escapes strings');

        // esc_raw()
        $this->diag('esc_raw()');
        $this->is(esc_raw('foo'), 'foo', 'esc_raw() returns the first argument as is');

        // esc_js()
        $this->diag('esc_js()');
        $this->is(esc_js('alert(\'foo\' + "bar")'), 'alert(&#039;foo&#039; + &quot;bar&quot;)', 'esc_js() escapes javascripts');

        // esc_js_no_entities()
        $this->diag('esc_js_no_entities()');
        $this->is(esc_js_no_entities('alert(\'foo\' + "bar")'), 'alert(\\\'foo\\\' + \\"bar\\")', 'esc_js_no_entities() escapes javascripts');
        $this->is(esc_js_no_entities('alert("hi\\there")'), 'alert(\\"hi\\\\there\\")', 'esc_js_no_entities() handles slashes correctly');
        $this->is(esc_js_no_entities('alert("été")'), 'alert(\\"été\\")', 'esc_js_no_entities() preserves utf-8');
        $output = <<<'EOF'
alert('hello
world')
EOF;
        $this->is(esc_js_no_entities(fix_linebreaks($output)), 'alert(\\\'hello\\nworld\\\')', 'esc_js_no_entities() handles linebreaks correctly');
        $this->is(esc_js_no_entities("alert('hello\nworld')"), 'alert(\\\'hello\\nworld\\\')', 'esc_js_no_entities() handles linebreaks correctly');
    }
}
