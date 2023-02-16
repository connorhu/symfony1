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
require_once __DIR__.'/../fixtures/myWebResponse2.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWebResponseTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dispatcher = new sfEventDispatcher();

        // ->initialize()
        $this->diag('->initialize()');
        $response = new myWebResponse2($dispatcher, array('charset' => 'ISO-8859-1'));
        $this->is($response->getContentType(), 'text/html; charset=ISO-8859-1', '->initialize() takes a "charset" option');
        $response = new myWebResponse2($dispatcher, array('content_type' => 'text/plain'));
        $this->is($response->getContentType(), 'text/plain; charset=utf-8', '->initialize() takes a "content_type" option');

        $response = new myWebResponse2($dispatcher);

        // ->getStatusCode() ->setStatusCode()
        $this->diag('->getStatusCode() ->setStatusCode()');
        $this->is($response->getStatusCode(), 200, '->getStatusCode() returns 200 by default');
        $response->setStatusCode(404);
        $this->is($response->getStatusCode(), 404, '->setStatusCode() sets status code');
        $this->is($response->getStatusText(), 'Not Found', '->setStatusCode() also sets the status text associated with the status code if no message is given');
        $response->setStatusCode(404, 'my text');
        $this->is($response->getStatusText(), 'my text', '->setStatusCode() takes a message as its second argument as the status text');
        $response->setStatusCode(404, '');
        $this->is($response->getStatusText(), '', '->setStatusCode() takes a message as its second argument as the status text');

        // ->hasHttpHeader()
        $this->diag('->hasHttpHeader()');
        $this->is($response->hasHttpHeader('non-existant'), false, '->hasHttpHeader() returns false if http header is not set');
        $response->setHttpHeader('My-Header', 'foo');
        $this->is($response->hasHttpHeader('My-Header'), true, '->hasHttpHeader() returns true if http header is not set');
        $this->is($response->hasHttpHeader('my-header'), true, '->hasHttpHeader() normalizes http header name');

        // ->getHttpHeader()
        $this->diag('->getHttpHeader()');
        $response->setHttpHeader('My-Header', 'foo');
        $this->is($response->getHttpHeader('My-Header'), 'foo', '->getHttpHeader() returns the current http header values');
        $this->is($response->getHttpHeader('my-header'), 'foo', '->getHttpHeader() normalizes http header name');

        // ->setHttpHeader()
        $this->diag('->setHttpHeader()');
        $response->setHttpHeader('My-Header', 'foo');
        $response->setHttpHeader('My-Header', 'bar', false);
        $response->setHttpHeader('my-header', 'foobar', false);
        $this->is($response->getHttpHeader('My-Header'), 'foo, bar, foobar', '->setHttpHeader() takes a replace argument as its third argument');
        $response->setHttpHeader('My-Other-Header', 'foo', false);
        $this->is($response->getHttpHeader('My-Other-Header'), 'foo', '->setHttpHeader() takes a replace argument as its third argument');

        $response->setHttpHeader('my-header', 'foo');
        $this->is($response->getHttpHeader('My-Header'), 'foo', '->setHttpHeader() normalizes http header name');

        // ->clearHttpHeaders()
        $this->diag('->clearHttpHeaders()');
        $response->setHttpHeader('my-header', 'foo');
        $response->clearHttpHeaders();
        $this->is($response->getHttpHeader('My-Header'), null, '->clearHttpHeaders() clears all current http headers');

        // ->getHttpHeaders()
        $this->diag('->getHttpHeaders()');
        $response->clearHttpHeaders();
        $response->setHttpHeader('my-header', 'foo');
        $response->setHttpHeader('my-header', 'bar', false);
        $response->setHttpHeader('another', 'foo');
        $this->is($response->getHttpHeaders(), array('My-Header' => 'foo, bar', 'Another' => 'foo'), '->getHttpHeaders() return all current response http headers');

        // ->normalizeHeaderName()
        $this->diag('->normalizeHeaderName()');
        foreach (array(
            array('header', 'Header'),
            array('HEADER', 'Header'),
            array('hEaDeR', 'Header'),
            array('my-header', 'My-Header'),
            array('my_header', 'My-Header'),
            array('MY_HEADER', 'My-Header'),
            array('my-header_is_very-long', 'My-Header-Is-Very-Long'),
            array('Content-Type', 'Content-Type'),
            array('content-type', 'Content-Type'),
        ) as $test) {
            $this->is($response->normalizeHeaderName($test[0]), $test[1], '->normalizeHeaderName() normalizes http header name');
        }

        // ->getContentType() ->setContentType()
        $this->diag('->getContentType() ->setContentType() ->getCharset()');

        $response = new myWebResponse2($dispatcher);
        $this->is($response->getContentType(), 'text/html; charset=utf-8', '->getContentType() returns a sensible default value');
        $this->is($response->getCharset(), 'utf-8', '->getCharset() returns the current charset of the response');

        $response->setContentType('text/xml');
        $this->is($response->getContentType(), 'text/xml; charset=utf-8', '->setContentType() adds a charset if none is given');

        $response->setContentType('application/vnd.mozilla.xul+xml');
        $this->is($response->getContentType(), 'application/vnd.mozilla.xul+xml; charset=utf-8', '->setContentType() adds a charset if none is given');
        $this->is($response->getCharset(), 'utf-8', '->getCharset() returns the current charset of the response');

        $response->setContentType('image/jpg');
        $this->is($response->getContentType(), 'image/jpg', '->setContentType() does not add a charset if the content-type is not text/*');

        $response->setContentType('text/xml; charset=ISO-8859-1');
        $this->is($response->getContentType(), 'text/xml; charset=ISO-8859-1', '->setContentType() does nothing if a charset is given');
        $this->is($response->getCharset(), 'ISO-8859-1', '->getCharset() returns the current charset of the response');

        $response->setContentType('text/xml;charset = ISO-8859-1');
        $this->is($response->getContentType(), 'text/xml;charset = ISO-8859-1', '->setContentType() does nothing if a charset is given');
        $this->is($response->getCharset(), 'ISO-8859-1', '->getCharset() returns the current charset of the response');

        $this->is($response->getContentType(), $response->getHttpHeader('content-type'), '->getContentType() is an alias for ->getHttpHeader(\'content-type\')');

        $response->setContentType('text/xml');
        $response->setContentType('text/html');
        $this->is($response->getHttpHeader('content-type'), 'text/html; charset=ISO-8859-1', '->setContentType() overrides previous content type if replace is true');

        // ->getTitle() ->setTitle() ->prependTitle
        $this->diag('->getTitle() ->setTitle() ->prependTitle()');
        $this->is($response->getTitle(), '', '->getTitle() returns an empty string by default');
        $response->setTitle('my title');
        $this->is($response->getTitle(), 'my title', '->setTitle() sets the title');
        $response->setTitle('fööbäär');
        $this->is($response->getTitle(), 'fööbäär', '->setTitle() will leave encoding intact');
        $response->setTitle(null);
        $this->is($response->getTitle(), '', '->setTitle() to null remove existing title');
        $response->prependTitle('my title');
        $this->is($response->getTitle(), 'my title', '->prependTitle() set title if no title has been set');
        $response->prependTitle('my subtitle');
        $this->is($response->getTitle(), 'my subtitle - my title', '->prependTitle() prepend title');
        $response->prependTitle('other title', ' | ');
        $this->is($response->getTitle(), 'other title | my subtitle - my title', '->prependTitle() prepend title with custom separator');

        // ->addHttpMeta()
        $this->diag('->addHttpMeta()');
        $response->clearHttpHeaders();
        $response->addHttpMeta('My-Header', 'foo');
        $response->addHttpMeta('My-Header', 'bar', false);
        $response->addHttpMeta('my-header', 'foobar', false);
        $metas = $response->getHttpMetas();
        $this->is($metas['My-Header'], 'foo, bar, foobar', '->addHttpMeta() takes a replace argument as its third argument');
        $this->is($response->getHttpHeader('My-Header'), 'foo, bar, foobar', '->addHttpMeta() also sets the corresponding http header');
        $response->addHttpMeta('My-Other-Header', 'foo', false);
        $metas = $response->getHttpMetas();
        $this->is($metas['My-Other-Header'], 'foo', '->addHttpMeta() takes a replace argument as its third argument');
        $response->addHttpMeta('my-header', 'foo');
        $metas = $response->getHttpMetas();
        $this->is($metas['My-Header'], 'foo', '->addHttpMeta() normalizes http header name');

        // ->addVaryHttpHeader()
        $this->diag('->addVaryHttpHeader()');
        $response->clearHttpHeaders();
        $response->addVaryHttpHeader('Cookie');
        $this->is($response->getHttpHeader('Vary'), 'Cookie', '->addVaryHttpHeader() adds a new Vary header');
        $response->addVaryHttpHeader('Cookie');
        $this->is($response->getHttpHeader('Vary'), 'Cookie', '->addVaryHttpHeader() does not add the same header twice');
        $response->addVaryHttpHeader('Accept-Language');
        $this->is($response->getHttpHeader('Vary'), 'Cookie, Accept-Language', '->addVaryHttpHeader() respects ordering');

        // ->addCacheControlHttpHeader()
        $this->diag('->addCacheControlHttpHeader()');
        $response->clearHttpHeaders();
        $response->addCacheControlHttpHeader('max-age', 0);
        $this->is($response->getHttpHeader('Cache-Control'), 'max-age=0', '->addCacheControlHttpHeader() adds a new Cache-Control header');
        $response->addCacheControlHttpHeader('max-age', 12);
        $this->is($response->getHttpHeader('Cache-Control'), 'max-age=12', '->addCacheControlHttpHeader() does not add the same header twice');
        $response->addCacheControlHttpHeader('no-cache');
        $this->is($response->getHttpHeader('Cache-Control'), 'max-age=12, no-cache', '->addCacheControlHttpHeader() respects ordering');

        // ->copyProperties()
        $this->diag('->copyProperties()');
        $response1 = new myWebResponse2($dispatcher);
        $response2 = new myWebResponse2($dispatcher);

        $response1->setHttpHeader('symfony', 'foo');
        $response1->setContentType('text/plain');
        $response1->setTitle('My title');

        $response2->copyProperties($response1);
        $this->is($response1->getHttpHeader('symfony'), $response2->getHttpHeader('symfony'), '->copyProperties() merges http headers');
        $this->is($response1->getContentType(), $response2->getContentType(), '->copyProperties() merges content type');
        $this->is($response1->getTitle(), $response2->getTitle(), '->copyProperties() merges titles');

        // ->addStylesheet()
        $this->diag('->addStylesheet()');
        $response = new myWebResponse2($dispatcher);
        $response->addStylesheet('test');
        $this->ok(array_key_exists('test', $response->getStylesheets()), '->addStylesheet() adds a new stylesheet for the response');
        $response->addStylesheet('foo', '');
        $this->ok(array_key_exists('foo', $response->getStylesheets()), '->addStylesheet() adds a new stylesheet for the response');
        $response->addStylesheet('first', 'first');
        $this->ok(array_key_exists('first', $response->getStylesheets('first')), '->addStylesheet() takes a position as its second argument');
        $response->addStylesheet('last', 'last');
        $this->ok(array_key_exists('last', $response->getStylesheets('last')), '->addStylesheet() takes a position as its second argument');
        $response->addStylesheet('bar', '', array('media' => 'print'));
        $stylesheets = $response->getStylesheets();
        $this->is($stylesheets['bar'], array('media' => 'print'), '->addStylesheet() takes an array of parameters as its third argument');

        try {
            $response->addStylesheet('last', 'none');
            $this->fail('->addStylesheet() throws an InvalidArgumentException if the position is not first, the empty string, or last');
        } catch (InvalidArgumentException $e) {
            $this->pass('->addStylesheet() throws an InvalidArgumentException if the position is not first, the empty string, or last');
        }

        // ->getStylesheets()
        $this->diag('->getStylesheets()');
        $this->is(array_keys($response->getStylesheets()), array('first', 'test', 'foo', 'bar', 'last'), '->getStylesheets() returns all current registered stylesheets ordered by position');
        $this->is($response->getStylesheets(''), array('test' => array(), 'foo' => array(), 'bar' => array('media' => 'print')), '->getStylesheets() takes a position as its first argument');
        $this->is($response->getStylesheets('first'), array('first' => array()), '->getStylesheets() takes a position as its first argument');
        $this->is($response->getStylesheets('last'), array('last' => array()), '->getStylesheets() takes a position as its first argument');

        // ->removeStylesheet()
        $this->diag('->removeStylesheet()');
        $response->removeStylesheet('foo');
        $this->is(array_keys($response->getStylesheets()), array('first', 'test', 'bar', 'last'), '->removeStylesheet() removes a stylesheet from the response');
        $response->removeStylesheet('first');
        $this->is(array_keys($response->getStylesheets()), array('test', 'bar', 'last'), '->removeStylesheet() removes a stylesheet from the response');

        // ->clearStylesheets()
        $this->diag('->clearStylesheets()');
        $response->clearStylesheets();
        $this->is($response->getStylesheets(), array(), '->clearStylesheets() removes all stylesheets from the response');

        // ->addJavascript()
        $this->diag('->addJavascript()');
        $response = new myWebResponse2($dispatcher);
        $response->addJavascript('test');
        $this->ok(array_key_exists('test', $response->getJavascripts()), '->addJavascript() adds a new javascript for the response');
        $response->addJavascript('foo', '', array('raw_name' => true));
        $this->ok(array_key_exists('foo', $response->getJavascripts()), '->addJavascript() adds a new javascript for the response');
        $response->addJavascript('first_js', 'first');
        $this->ok(array_key_exists('first_js', $response->getJavascripts('first')), '->addJavascript() takes a position as its second argument');
        $response->addJavascript('last_js', 'last');
        $this->ok(array_key_exists('last_js', $response->getJavascripts('last')), '->addJavascript() takes a position as its second argument');

        try {
            $response->addJavascript('last_js', 'none');
            $this->fail('->addJavascript() throws an InvalidArgumentException if the position is not first, the empty string, or last');
        } catch (InvalidArgumentException $e) {
            $this->pass('->addJavascript() throws an InvalidArgumentException if the position is not first, the empty string, or last');
        }

        // ->getJavascripts()
        $this->diag('->getJavascripts()');
        $this->is(array_keys($response->getJavascripts()), array('first_js', 'test', 'foo', 'last_js'), '->getJavascripts() returns all current registered javascripts ordered by position');
        $this->is($response->getJavascripts(''), array('test' => array(), 'foo' => array('raw_name' => true)), '->getJavascripts() takes a position as its first argument');
        $this->is($response->getJavascripts('first'), array('first_js' => array()), '->getJavascripts() takes a position as its first argument');
        $this->is($response->getJavascripts('last'), array('last_js' => array()), '->getJavascripts() takes a position as its first argument');

        // ->removeJavascript()
        $this->diag('->removeJavascript()');
        $response->removeJavascript('test');
        $this->is(array_keys($response->getJavascripts()), array('first_js', 'foo', 'last_js'), '->removeJavascripts() removes a javascript file');
        $response->removeJavascript('first_js');
        $this->is(array_keys($response->getJavascripts()), array('foo', 'last_js'), '->removeJavascripts() removes a javascript file');

        // ->clearJavascripts()
        $this->diag('->clearJavascripts()');
        $response->clearJavascripts();
        $this->is($response->clearJavascripts(), null, '->clearJavascripts() removes all javascripts from the response');

        // ->setCookie() ->getCookies()
        $this->diag('->setCookie() ->getCookies()');
        $response->setCookie('foo', 'bar');
        $this->is($response->getCookies(), array('foo' => array('name' => 'foo', 'value' => 'bar', 'expire' => null, 'path' => '/', 'domain' => '', 'secure' => false, 'httpOnly' => false)), '->setCookie() adds a cookie for the response');

        // ->setHeaderOnly() ->getHeaderOnly()
        $this->diag('->setHeaderOnly() ->isHeaderOnly()');
        $response = new myWebResponse2($dispatcher);
        $this->is($response->isHeaderOnly(), false, '->isHeaderOnly() returns false if the content must be send to the client');
        $response->setHeaderOnly(true);
        $this->is($response->isHeaderOnly(), true, '->setHeaderOnly() changes the current value of header only');

        // ->sendContent()
        $this->diag('->sendContent()');
        $response->setHeaderOnly(true);
        $response->setContent('foo');
        ob_start();
        $response->sendContent();
        $this->is(ob_get_clean(), '', '->sendContent() returns nothing if headerOnly is true');

        $response->setHeaderOnly(false);
        $response->setContent('foo');
        ob_start();
        $response->sendContent();
        $this->is(ob_get_clean(), 'foo', '->sendContent() returns the response content if headerOnly is false');

        // ->serialize() ->unserialize()
        $this->diag('->serialize() ->unserialize()');
        $resp = unserialize(serialize($response));
        $resp->initialize($dispatcher);
        $this->ok($response == $resp, 'sfWebResponse implements the Serializable interface');
    }
}
