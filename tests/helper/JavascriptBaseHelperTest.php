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
require_once __DIR__.'/../../lib/helper/JavascriptBaseHelper.php';

/**
 * @internal
 *
 * @coversNothing
 */
class JavascriptBaseHelperTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        // boolean_for_javascript()
        $this->diag('boolean_for_javascript()');
        $this->is(boolean_for_javascript(true), 'true', 'boolean_for_javascript() makes a javascript representation of the boolean if the param is boolean');
        $this->is(boolean_for_javascript(false), 'false', 'boolean_for_javascript() makes a javascript representation of the boolean if the param is boolean');
        $this->is(boolean_for_javascript(1 == 0), 'false', 'boolean_for_javascript() makes a javascript representation of the boolean if the param is boolean');
        $this->is(boolean_for_javascript('dummy'), 'dummy', 'boolean_for_javascript() makes a javascript representation of the boolean if the param is boolean');

        // options_for_javascript()
        $this->diag('options_for_javascript()');
        $this->is(options_for_javascript(array("'a'" => "'b'", "'c'" => false)), "{'a':'b', 'c':false}", 'options_for_javascript() makes a javascript representation of the passed array');
        $this->is(options_for_javascript(array("'a'" => array("'b'" => "'c'"))), "{'a':{'b':'c'}}", 'options_for_javascript() works with nested arrays');

        // javascript_tag()
        $this->diag('javascript_tag()');
        $expect = <<<'EOT'
<script type="text/javascript">
//<![CDATA[
alert("foo");
//]]>
</script>
EOT;
        $this->is(javascript_tag('alert("foo");'), $expect, 'javascript_tag() takes the content as string parameter');

        // link_to_function()
        $this->diag('link_to_function()');
        $this->is(link_to_function('foo', 'alert(\'bar\')'), '<a href="#" onclick="alert(\'bar\'); return false;">foo</a>', 'link_to_function generates a link with onClick handler for function');
        // #4152
        $this->is(link_to_function('foo', 'alert(\'bar\')', array('confirm' => 'sure?')), '<a href="#" onclick="if(window.confirm(\'sure?\')){ alert(\'bar\');}; return false;">foo</a>', 'link_to_function works fine with confirm dialog');
    }
}
