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
class sfDomCssSelectorTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $html = <<<'EOF'
        <html>
          <head>
          </head>
          <body>
            <h1>Test page</h1>
        
            <h2>Title 1</h2>
            <p class="header">header</p>
            <p class="foo bar foobar">multi-classes</p>
            <p class="myfoo">myfoo</p>
            <p class="myfoo" id="mybar">myfoo bis</p>
        
            <p onclick="javascript:alert('with a . and a # inside an attribute');">works great</p>
        
            <select>
              <option value="0">foo input</option>
            </select>
        
            <div id="simplelist">
              <ul id="list">
                <li>First</li>
                <li>Second with a <a href="http://www.google.com/" class="foo1 bar1 bar1-foo1 foobar1">link</a></li>
              </ul>
        
              <ul id="anotherlist">
                <li>First</li>
                <li>Second</li>
                <li>Third with <a class="bar1-foo1">another link</a></li>
              </ul>
            </div>
        
            <h2>Title 2</h2>
            <ul id="mylist">
              <li>element 1</li>
              <li>element 2</li>
              <li>
                <ul>
                  <li>element 3</li>
                  <li>element 4</li>
                </ul>
              </li>
            </ul>
        
            <div id="combinators">
              <ul>
                <li>test 1</li>
                <li>test 2</li>
                <ul>
                  <li>test 3</li>
                  <li>test 4</li>
                </ul>
              </ul>
            </div>
        
            <div id="adjacent_bug">
              <p>First paragraph</p>
              <p>Second paragraph</p>
              <p>Third <a href='#'>paragraph</a></p>
            </div>
        
            <div id="footer">footer</div>
          </body>
        </html>
        EOF;

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->validateOnParse = true;
        $dom->loadHTML($html);

        $c = new sfDomCssSelector($dom);

        // ->matchAll()
        $this->diag('->matchAll()');

        $this->diag('basic selectors');
        $this->is($c->matchAll('h1')->getValues(), array('Test page'), '->matchAll() takes a CSS selector as its first argument');
        $this->is($c->matchAll('h2')->getValues(), array('Title 1', 'Title 2'), '->matchAll() returns an array of matching texts');
        $this->is($c->matchAll('#footer')->getValues(), array('footer'), '->matchAll() supports searching html elements by id');
        $this->is($c->matchAll('div#footer')->getValues(), array('footer'), '->matchAll() supports searching html elements by id for a tag name');
        $this->is($c->matchAll('*[class="myfoo"]')->getValues(), array('myfoo', 'myfoo bis'), '->matchAll() can take a * to match every elements');
        $this->is($c->matchAll('*[class=myfoo]')->getValues(), array('myfoo', 'myfoo bis'), '->matchAll() can take a * to match every elements');
        $this->is($c->matchAll('*[value="0"]')->getValues(), array('foo input'), '->matchAll() can take a * to match every elements');

        $this->is($c->matchAll('.header')->getValues(), array('header'), '->matchAll() supports searching html elements by class name');
        $this->is($c->matchAll('p.header')->getValues(), array('header'), '->matchAll() supports searching html elements by class name for a tag name');
        $this->is($c->matchAll('div.header')->getValues(), array(), '->matchAll() supports searching html elements by class name for a tag name');
        $this->is($c->matchAll('*.header')->getValues(), array('header'), '->matchAll() supports searching html elements by class name');

        $this->is($c->matchAll('.foo')->getValues(), array('multi-classes'), '->matchAll() supports searching html elements by class name for multi-class elements');
        $this->is($c->matchAll('.bar')->getValues(), array('multi-classes'), '->matchAll() supports searching html elements by class name for multi-class elements');
        $this->is($c->matchAll('.foobar')->getValues(), array('multi-classes'), '->matchAll() supports searching html elements by class name for multi-class elements');

        $this->is($c->matchAll('ul#mylist ul li')->getValues(), array('element 3', 'element 4'), '->matchAll() supports searching html elements by several selectors');

        $this->is($c->matchAll('#nonexistant')->getValues(), array(), '->matchAll() returns an empty array if the id does not exist');

        $this->is($c->matchAll('.bar1-foo1')->getValues(), array('link', 'another link'), 'Hyphenated class names are matched correctly');

        $this->diag('attribute selectors');
        $this->is($c->matchAll('ul#list li a[href]')->getValues(), array('link'), '->matchAll() supports checking attribute existence');
        $this->is($c->matchAll('ul#list li a[class~="foo1"]')->getValues(), array('link'), '->matchAll() supports checking attribute word matching');
        $this->is($c->matchAll('ul#list li a[class~="bar1"]')->getValues(), array('link'), '->matchAll() supports checking attribute word matching');
        $this->is($c->matchAll('ul#list li a[class~="foobar1"]')->getValues(), array('link'), '->matchAll() supports checking attribute word matching');
        $this->is($c->matchAll('ul#list li a[class^="foo1"]')->getValues(), array('link'), '->matchAll() supports checking attribute starting with');
        $this->is($c->matchAll('ul#list li a[class$="foobar1"]')->getValues(), array('link'), '->matchAll() supports checking attribute ending with');
        $this->is($c->matchAll('ul#list li a[class*="oba"]')->getValues(), array('link'), '->matchAll() supports checking attribute with *');
        $this->is($c->matchAll('ul#list li a[href="http://www.google.com/"]')->getValues(), array('link'), '->matchAll() supports checking attribute word matching');
        $this->is($c->matchAll('ul#anotherlist li a[class|="bar1"]')->getValues(), array('another link'), '->matchAll() supports checking attribute starting with value followed by optional hyphen');

        $this->is($c->matchAll('ul#list li a[class*="oba"][class*="ba"]')->getValues(), array('link'), '->matchAll() supports chaining attribute selectors');
        $this->is($c->matchAll('p[class="myfoo"][id="mybar"]')->getValues(), array('myfoo bis'), '->matchAll() supports chaining attribute selectors');

        $this->is($c->matchAll('p[onclick*="a . and a #"]')->getValues(), array('works great'), '->matchAll() support . # and spaces in attribute selectors');

        $this->diag('combinators');
        $this->is($c->matchAll('body  h1')->getValues(), array('Test page'), '->matchAll() takes a CSS selectors separated by one or more spaces');
        $this->is($c->matchAll('div#combinators > ul  >   li')->getValues(), array('test 1', 'test 2'), '->matchAll() support > combinator');
        $this->is($c->matchAll('div#combinators>ul>li')->getValues(), array('test 1', 'test 2'), '->matchAll() support > combinator with optional surrounding spaces');
        $this->is($c->matchAll('div#combinators li  +   li')->getValues(), array('test 2', 'test 4'), '->matchAll() support + combinator');
        $this->is($c->matchAll('div#combinators li+li')->getValues(), array('test 2', 'test 4'), '->matchAll() support + combinator with optional surrounding spaces');

        $this->is($c->matchAll('h1, h2')->getValues(), array('Test page', 'Title 1', 'Title 2'), '->matchAll() takes a multiple CSS selectors separated by a ,');
        $this->is($c->matchAll('h1,h2')->getValues(), array('Test page', 'Title 1', 'Title 2'), '->matchAll() takes a multiple CSS selectors separated by a ,');
        $this->is($c->matchAll('h1  ,   h2')->getValues(), array('Test page', 'Title 1', 'Title 2'), '->matchAll() takes a multiple CSS selectors separated by a ,');
        $this->is($c->matchAll('h1, h1,h1')->getValues(), array('Test page'), '->matchAll() returns nodes only once for multiple selectors');
        $this->is($c->matchAll('h1,h2,h1')->getValues(), array('Test page', 'Title 1', 'Title 2'), '->matchAll() returns nodes only once for multiple selectors');

        $this->is($c->matchAll('p[onclick*="a . and a #"], div#combinators > ul li + li')->getValues(), array('works great', 'test 2', 'test 4'), '->matchAll() mega example!');

        $this->is($c->matchAll('.myfoo:contains("bis")')->getValues(), array('myfoo bis'), '->matchAll() :contains()');
        $this->is($c->matchAll('.myfoo:eq(1)')->getValues(), array('myfoo bis'), '->matchAll() :eq()');
        $this->is($c->matchAll('.myfoo:last')->getValues(), array('myfoo bis'), '->matchAll() :last');
        $this->is($c->matchAll('.myfoo:first')->getValues(), array('myfoo'), '->matchAll() :first');
        $this->is($c->matchAll('h2:first')->getValues(), array('Title 1'), '->matchAll() :first');
        $this->is($c->matchAll('p.myfoo:first')->getValues(), array('myfoo'), '->matchAll() :first');
        $this->is($c->matchAll('p:lt(2)')->getValues(), array('header', 'multi-classes'), '->matchAll() :lt');
        $this->is($c->matchAll('p:gt(2)')->getValues(), array('myfoo bis', 'works great', 'First paragraph', 'Second paragraph', 'Third paragraph'), '->matchAll() :gt');
        $this->is($c->matchAll('p:odd')->getValues(), array('multi-classes', 'myfoo bis', 'First paragraph', 'Third paragraph'), '->matchAll() :odd');
        $this->is($c->matchAll('p:even')->getValues(), array('header', 'myfoo', 'works great', 'Second paragraph'), '->matchAll() :even');
        $this->is($c->matchAll('#simplelist li:first-child')->getValues(), array('First', 'First'), '->matchAll() :first-child');
        $this->is($c->matchAll('#simplelist li:nth-child(1)')->getValues(), array('First', 'First'), '->matchAll() :nth-child');
        $this->is($c->matchAll('#simplelist li:nth-child(2)')->getValues(), array('Second with a link', 'Second'), '->matchAll() :nth-child');
        $this->is($c->matchAll('#simplelist li:nth-child(3)')->getValues(), array('Third with another link'), '->matchAll() :nth-child');
        $this->is($c->matchAll('#simplelist li:last-child')->getValues(), array('Second with a link', 'Third with another link'), '->matchAll() :last-child');

        $this->diag('combinations of pseudo-selectors');
        $this->is($c->matchAll('.myfoo:contains("myfoo"):contains("bis")')->getValues(), array('myfoo bis'), '->matchAll() :contains():contains()');
        $this->is($c->matchAll('.myfoo:contains("myfoo"):last')->getValues(), array('myfoo bis'), '->matchAll() :contains():last');
        $this->is($c->matchAll('.myfoo:last:contains("foobarbaz")')->getValues(), array(), '->matchAll() :last:contains()');
        $this->is($c->matchAll('.myfoo:contains("myfoo"):contains(\'bis\'):contains(foo)')->getValues(), array('myfoo bis'), '->matchAll() :contains() supports different quote styles');

        // ->matchAll()
        $this->diag('->matchAll()');
        $this->is($c->matchAll('ul')->matchAll('li')->getValues(), $c->matchAll('ul li')->getValues(), '->matchAll() returns a new sfDomCssSelector restricted to the result nodes');

        // ->matchSingle()
        $this->diag('->matchSingle()');
        $this->is(array($c->matchAll('ul li')->getValue()), $c->matchSingle('ul li')->getValues(), '->matchSingle() returns a new sfDomCssSelector restricted to the first result node');

        // ->getValues()
        $this->diag('->getValues()');
        $this->is($c->matchAll('p.myfoo')->getValues(), array('myfoo', 'myfoo bis'), '->getValues() returns all node values');

        // ->getValue()
        $this->diag('->getValue()');
        $this->is($c->matchAll('h1')->getValue(), 'Test page', '->getValue() returns the first node value');

        $this->is($c->matchAll('#adjacent_bug > p')->getValues(), array('First paragraph', 'Second paragraph', 'Third paragraph'), '->matchAll() suppports the + combinator');
        $this->is($c->matchAll('#adjacent_bug > p > a')->getValues(), array('paragraph'), '->matchAll() suppports the + combinator');
        $this->is($c->matchAll('#adjacent_bug p + p')->getValues(), array('Second paragraph', 'Third paragraph'), '->matchAll() suppports the + combinator');
        $this->is($c->matchAll('#adjacent_bug > p + p')->getValues(), array('Second paragraph', 'Third paragraph'), '->matchAll() suppports the + combinator');
        $this->is($c->matchAll('#adjacent_bug > p + p > a')->getValues(), array('paragraph'), '->matchAll() suppports the + combinator');

        // Iterator interface
        $this->diag('Iterator interface');
        foreach ($c->matchAll('h2') as $key => $value) {
            switch ($key) {
                case 0:
                    $this->is($value->nodeValue, 'Title 1', 'The object is an iterator');
                    break;
                case 1:
                    $this->is($value->nodeValue, 'Title 2', 'The object is an iterator');
                    break;
                default:
                    $this->fail('The object is an iterator');
            }
        }

        // Countable interface
        $this->diag('Countable interface');
        $this->is(count($c->matchAll('h1')), 1, 'sfDomCssSelector implements Countable');
        $this->is(count($c->matchAll('h2')), 2, 'sfDomCssSelector implements Countable');
    }
}
