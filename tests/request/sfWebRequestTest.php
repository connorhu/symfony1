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
require_once __DIR__.'/../fixtures/myRequest4.php';
require_once __DIR__.'/../fixtures/BaseForm.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWebRequestTest extends TestCase
{
    use PhpUnitSfTestHelperTrait;

    public function testTodoMigrate()
    {
        $dispatcher = new sfEventDispatcher();
        $request = new myRequest4($dispatcher);

        // ->getLanguages()
        $this->diag('->getLanguages()');

        $this->is($request->getLanguages(), array(), '->getLanguages() returns an empty array if the client do not send an ACCEPT_LANGUAGE header');

        $request->languages = null;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
        $this->is($request->getLanguages(), array(), '->getLanguages() returns an empty array if the client send an empty ACCEPT_LANGUAGE header');

        $request->languages = null;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '0';
        $this->is($request->getLanguages(), array(), '->getLanguages() returns an empty array if the client send ACCEPT_LANGUAGE header with 0 value');

        $request->languages = null;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,en;q=0.5,fr;q=0.3';
        $this->is($request->getLanguages(), array('en_US', 'en', 'fr'), '->getLanguages() returns an array with all accepted languages');

        $request->languages = null;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'i-cherokee';
        $this->is($request->getLanguages(), array('cherokee'), '->getLanguages() returns an array with all accepted languages');

        // ->getPreferredCulture()
        $this->diag('->getPreferredCulture()');

        $request->languages = array('fr');
        $this->is($request->getPreferredCulture(), 'fr', '->getPreferredCulture() returns the first given languages if no parameter given');

        $request->languages = null;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
        $this->is($request->getPreferredCulture(array('fr', 'en')), 'fr', '->getPreferredCulture() returns the first given culture if the client do not send an ACCEPT_LANGUAGE header');

        $request->languages = null;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,en;q=0.5,fr;q=0.3';
        $this->is($request->getPreferredCulture(array('fr', 'en')), 'en', '->getPreferredCulture() returns the preferred culture');

        $request->languages = null;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,en;q=0.5,fr';
        $this->is($request->getPreferredCulture(array('fr', 'en')), 'fr', '->getPreferredCulture() returns the preferred culture');

        // ->getCharsets()
        $this->diag('->getCharsets()');

        $this->is($request->getCharsets(), array(), '->getCharsets() returns an empty array if the client do not send an ACCEPT_CHARSET header');

        $request->charsets = array('ISO-8859-1');
        $this->is($request->getCharsets(), array('ISO-8859-1'), '->getCharsets() returns an empty array if charsets are already defined');

        $request->charsets = null;
        $_SERVER['HTTP_ACCEPT_CHARSET'] = '';
        $this->is($request->getCharsets(), array(), '->getCharsets() returns an empty array if the client send an empty ACCEPT_CHARSET header');

        $request->charsets = null;
        $_SERVER['HTTP_ACCEPT_CHARSET'] = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
        $this->is($request->getCharsets(), array('ISO-8859-1', 'utf-8', '*'), '->getCharsets() returns an array with all accepted charsets');

        // ->getAcceptableContentTypes()
        $this->diag('->getAcceptableContentTypes()');

        $this->is($request->getAcceptableContentTypes(), array(), '->getAcceptableContentTypes() returns an empty array if the client do not send an ACCEPT header');

        $request->acceptableContentTypes = array('text/xml');
        $this->is($request->getAcceptableContentTypes(), array('text/xml'), '->getAcceptableContentTypes() returns an empty array if acceptableContentTypes are already set');

        $request->acceptableContentTypes = null;
        $_SERVER['HTTP_ACCEPT'] = '';
        $this->is($request->getAcceptableContentTypes(), array(), '->getAcceptableContentTypes() returns an empty array if the client send an empty ACCEPT header');

        $request->acceptableContentTypes = null;
        $_SERVER['HTTP_ACCEPT'] = 'text/xml,application/xhtml+xml,application/xml,text/html;q=0.9,text/plain;q=0.8,*/*;q=0.5';
        $this->is($request->getAcceptableContentTypes(), array('text/xml', 'application/xhtml+xml', 'application/xml', 'text/html', 'text/plain', '*/*'), '->getAcceptableContentTypes() returns an array with all accepted content types');

        // ->splitHttpAcceptHeader()
        $this->diag('->splitHttpAcceptHeader()');

        $this->is($request->splitHttpAcceptHeader(''), array(), '->splitHttpAcceptHeader() returns an empty array if the header is empty');
        $this->is($request->splitHttpAcceptHeader('a,b,c'), array('a', 'b', 'c'), '->splitHttpAcceptHeader() returns an array of values');
        $this->is($request->splitHttpAcceptHeader('a,b;q=0.7,c;q=0.3'), array('a', 'b', 'c'), '->splitHttpAcceptHeader() strips the q value');
        $this->is($request->splitHttpAcceptHeader('a;q=0.1,b,c;q=0.3'), array('b', 'c', 'a'), '->splitHttpAcceptHeader() sorts values by the q value');
        $this->is($request->splitHttpAcceptHeader('a;q=0.3,b,c;q=0.3'), array('b', 'a', 'c'), '->splitHttpAcceptHeader() sorts values by the q value including equal values');
        $this->is($request->splitHttpAcceptHeader('a; q=0.1, b, c; q=0.3'), array('b', 'c', 'a'), '->splitHttpAcceptHeader() trims whitespaces');
        $this->is($request->splitHttpAcceptHeader('a; q=0, b'), array('b'), '->splitHttpAcceptHeader() removes values when q = 0 (as per the RFC)');

        // ->getRequestFormat() ->setRequestFormat()
        $this->diag('->getRequestFormat() ->setRequestFormat()');

        $this->ok(null === $request->getRequestFormat(), '->getRequestFormat() returns null if the format is not defined in the request');
        $request->setParameter('sf_format', 'js');
        $this->is($request->getRequestFormat(), 'js', '->getRequestFormat() returns the request format');

        $request->setRequestFormat('css');
        $this->is($request->getRequestFormat(), 'css', '->setRequestFormat() sets the request format');

        // ->getFormat() ->setFormat()
        $this->diag('->getFormat() ->setFormat()');

        $customRequest = new myRequest4($dispatcher, array(), array(), array('formats' => array('custom' => 'application/custom')));
        $this->is($customRequest->getFormat('application/custom'), 'custom', '->getFormat() returns the format for the given mime type if when is set as initialisation option');

        $request->setFormat('js', 'application/x-javascript');
        $this->is($request->getFormat('application/x-javascript'), 'js', '->getFormat() returns the format for the given mime type');
        $request->setFormat('js', array('application/x-javascript', 'text/js'));
        $this->is($request->getFormat('text/js'), 'js', '->setFormat() can take an array of mime types');
        $this->is($request->getFormat('foo/bar'), null, '->getFormat() returns null if the mime type does not exist');

        // ->getMimeType()
        $this->diag('->getMimeType()');

        $this->is($request->getMimeType('js'), 'application/x-javascript', '->getMimeType() returns the first mime type for the given format');
        $this->is($request->getMimeType('foo'), null, '->getMimeType() returns null if the format does not exist');

        // ->isSecure()
        $this->diag('->isSecure()');

        $this->is($request->isSecure(), false, '->isSecure() returns false if request is not secure');

        $_SERVER['HTTPS'] = 'ON';
        $this->is($request->isSecure(), true, '->isSecure() checks the "HTTPS" environment variable');
        $_SERVER['HTTPS'] = 'on';
        $this->is($request->isSecure(), true, '->isSecure() checks the "HTTPS" environment variable');
        $_SERVER['HTTPS'] = '1';
        $this->is($request->isSecure(), true, '->isSecure() checks the "HTTPS" environment variable');
        $request->resetPathInfoArray();

        $_SERVER['HTTP_SSL_HTTPS'] = 'ON';
        $this->is($request->isSecure(), true, '->isSecure() checks the "HTTP_SSL_HTTPS" environment variable');
        $_SERVER['HTTP_SSL_HTTPS'] = 'on';
        $this->is($request->isSecure(), true, '->isSecure() checks the "HTTP_SSL_HTTPS" environment variable');
        $_SERVER['HTTP_SSL_HTTPS'] = '1';
        $this->is($request->isSecure(), true, '->isSecure() checks the "HTTP_SSL_HTTPS" environment variable');
        $request->resetPathInfoArray();

        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->is($request->isSecure(), true, '->isSecure() checks the "HTTP_X_FORWARDED_PROTO" environment variable');
        $request->resetPathInfoArray();

        $request->setOption('trust_proxy', false);

        $_SERVER['HTTP_SSL_HTTPS'] = '1';
        $this->is($request->isSecure(), false, '->isSecure() not checks the "HTTP_SSL_HTTPS" environment variable when "trust_proxy" option is set to false');
        $request->resetPathInfoArray();

        $_SERVER['HTTP_X_FORWARDED_PROTO'] = '1';
        $this->is($request->isSecure(), false, '->isSecure() not checks the "HTTP_X_FORWARDED_PROTO" environment variable when "trust_proxy" option is set to false');
        $request->resetPathInfoArray();

        $request->setOption('trust_proxy', true);

        // ->getUriPrefix()
        $this->diag('->getUriPrefix()');

        $request->resetPathInfoArray();
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_HOST'] = 'symfony-project.org:80';
        $this->is($request->getUriPrefix(), 'http://symfony-project.org', '->getUriPrefix() returns no port for standard http port');
        $_SERVER['HTTP_HOST'] = 'symfony-project.org';
        $this->is($request->getUriPrefix(), 'http://symfony-project.org', '->getUriPrefix() works fine with no port in HTTP_HOST');
        $_SERVER['HTTP_HOST'] = 'symfony-project.org:8088';
        $this->is($request->getUriPrefix(), 'http://symfony-project.org:8088', '->getUriPrefix() works for nonstandard http ports');

        $request->resetPathInfoArray();
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['HTTP_HOST'] = 'symfony-project.org:443';
        $this->is($request->getUriPrefix(), 'https://symfony-project.org', '->getUriPrefix() returns no port for standard https port');
        $_SERVER['HTTP_HOST'] = 'symfony-project.org';
        $this->is($request->getUriPrefix(), 'https://symfony-project.org', '->getUriPrefix() works fine with no port in HTTP_HOST');
        $_SERVER['HTTP_HOST'] = 'symfony-project.org:8043';
        $this->is($request->getUriPrefix(), 'https://symfony-project.org:8043', '->getUriPrefix() works for nonstandard https ports');

        $request->resetPathInfoArray();
        $_SERVER['HTTP_HOST'] = 'symfony-project.org';
        $_SERVER['SERVER_PORT'] = '8080';
        $this->is($request->getUriPrefix(), 'http://symfony-project.org:8080', '->getUriPrefix() uses the "SERVER_PORT" environment variable');

        $request->resetPathInfoArray();
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'symfony-project.org';
        $_SERVER['SERVER_PORT'] = '8043';
        $this->is($request->getUriPrefix(), 'https://symfony-project.org:8043', '->getUriPrefix() uses the "SERVER_PORT" environment variable');

        $request->resetPathInfoArray();
        $request->setOption('http_port', '8080');
        $_SERVER['HTTP_HOST'] = 'symfony-project.org';
        $this->is($request->getUriPrefix(), 'http://symfony-project.org:8080', '->getUriPrefix() uses the configured port');
        $request->setOption('http_port', null);

        $request->resetPathInfoArray();
        $request->setOption('https_port', '8043');
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'symfony-project.org';
        $this->is($request->getUriPrefix(), 'https://symfony-project.org:8043', '->getUriPrefix() uses the configured port');
        $request->setOption('https_port', null);

        $request->resetPathInfoArray();
        $_SERVER['HTTP_HOST'] = 'symfony-project.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->is($request->getUriPrefix(), 'https://symfony-project.org', '->getUriPrefix() works on secure requests forwarded as non-secure requests');

        $request->resetPathInfoArray();
        $request->setOption('https_port', '8043');
        $_SERVER['HTTP_HOST'] = 'symfony-project.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->is($request->getUriPrefix(), 'https://symfony-project.org:8043', '->getUriPrefix() uses the configured port on secure requests forwarded as non-secure requests');

        $request->resetPathInfoArray();

        // ->getRemoteAddress()
        $this->diag('->getRemoteAddress()');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->is($request->getRemoteAddress(), '127.0.0.1', '->getRemoteAddress() returns the remote address');

        // ->getForwardedFor()
        $this->diag('->getForwardedFor()');

        $this->is($request->getForwardedFor(), null, '->getForwardedFor() returns null if the request was not forwarded.');
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1, 10.0.0.2';
        $this->is_deeply($request->getForwardedFor(), array('10.0.0.1', '10.0.0.2'), '->getForwardedFor() returns the value from HTTP_X_FORWARDED_FOR');

        // ->getClientIp()
        $this->diag('->getClientIp()');

        $_SERVER['HTTP_CLIENT_IP'] = '127.1.1.1';
        $this->is($request->getClientIp(), '127.1.1.1', '->getClientIp() returns the value from HTTP_CLIENT_IP if it exists');
        unset($_SERVER['HTTP_CLIENT_IP']);

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1, 10.0.0.2';
        $this->is($request->getClientIp(), '10.0.0.1', '->getClientIp() returns the first HTTP_X_FORWARDED_FOR if it exists');
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);

        $_SERVER['HTTP_CLIENT_IP'] = '127.1.1.1';
        $this->is($request->getClientIp(false), '127.0.0.1', '->getClientIp() returns the remote address even if HTTP_CLIENT_IP exists when "proxy" argument is set to false');
        unset($_SERVER['HTTP_CLIENT_IP']);

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1, 10.0.0.2';
        $this->is($request->getClientIp(false), '127.0.0.1', '->getClientIp() returns the remote address even if HTTP_X_FORWARDED_FOR exists when "proxy" argument is set to false');
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);

        $this->is($request->getClientIp(false), '127.0.0.1', '->getClientIp() returns remote address by default');

        $request->setOption('trust_proxy', false);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1, 10.0.0.2';
        $this->is($request->getClientIp(), '127.0.0.1', '->getClientIp() returns the remote address even if HTTP_X_FORWARDED_FOR exists when "trust_proxy" is set ot false');
        $request->setOption('trust_proxy', true);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);

        // ->getGetParameters() ->getGetParameter()
        $this->diag('->getGetParameters() ->getGetParameter()');

        $_GET['get_param'] = 'value';
        $request = new myRequest4($dispatcher);
        $this->is($request->getGetParameters(), array('get_param' => 'value'), '->getGetParameters() returns GET parameters');
        $this->is($request->getGetParameter('get_param'), 'value', '->getGetParameter() returns GET parameter by name');
        unset($_GET['get_param']);

        // ->getPostParameters() ->getPostParameter()
        $this->diag('->getPostParameters() ->getPostParameter()');

        $_POST['post_param'] = 'value';
        $request = new myRequest4($dispatcher);
        $this->is($request->getPostParameters(), array('post_param' => 'value'), '->getPostParameters() returns POST parameters');
        $this->is($request->getPostParameter('post_param'), 'value', '->getPostParameter() returns POST parameter by name');
        unset($_POST['post_param']);

        // ->getMethod()
        $this->diag('->getMethod()');

        $_SERVER['REQUEST_METHOD'] = 'none';
        $request = new myRequest4($dispatcher);
        $this->is($request->getMethod(), 'GET', '->getMethod() returns GET by default');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new myRequest4($dispatcher);
        $this->is($request->getMethod(), 'GET', '->getMethod() returns GET if the method is GET');

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request = new myRequest4($dispatcher);
        $this->is($request->getMethod(), 'PUT', '->getMethod() returns PUT if the method is PUT');

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $request = new myRequest4($dispatcher, array(), array(), array('content_custom_only_for_test' => 'first=value'));
        $this->is($request->getPostParameter('first'), 'value', '->getMethod() set POST parameters from parsed content if content type is "application/x-www-form-urlencoded" and the method is PUT');
        unset($_POST['first'], $_SERVER['CONTENT_TYPE']);

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $request = new myRequest4($dispatcher);
        $this->is($request->getMethod(), 'DELETE', '->getMethod() returns DELETE if the method is DELETE');

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $request = new myRequest4($dispatcher, array(), array(), array('content_custom_only_for_test' => 'first=value'));
        $this->is($request->getPostParameter('first'), 'value', '->getMethod() set POST parameters from parsed content if content type is "application/x-www-form-urlencoded" and the method is DELETE');
        unset($_POST['first'], $_SERVER['CONTENT_TYPE']);

        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $request = new myRequest4($dispatcher);
        $this->is($request->getMethod(), 'HEAD', '->getMethod() returns DELETE if the method is HEAD');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['sf_method'] = 'PUT';
        $request = new myRequest4($dispatcher);
        $this->is($request->getMethod(), 'PUT', '->getMethod() returns the "sf_method" parameter value if it exists and if the method is POST');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['sf_method'] = 'PUT';
        $request = new myRequest4($dispatcher);
        $this->is($request->getMethod(), 'GET', '->getMethod() returns the "sf_method" parameter value if it exists and if the method is POST');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_POST['sf_method']);
        $request = new myRequest4($dispatcher);
        $this->is($request->getMethod(), 'POST', '->getMethod() returns the "sf_method" parameter value if it exists and if the method is POST');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['sf_method'] = 'PUT';
        $request = new myRequest4($dispatcher);
        $this->is($request->getMethod(), 'PUT', '->getMethod() returns the "sf_method" parameter value if it exists and if the method is POST');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_GET['sf_method']);
        $request = new myRequest4($dispatcher);
        $this->is($request->getMethod(), 'POST', '->getMethod() returns the "sf_method" parameter value if it exists and if the method is POST');

        // ->isMethod()
        $this->diag('->isMethod()');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new myRequest4($dispatcher);
        $this->ok($request->isMethod('POST'), '->isMethod() returns true if the method is POST');

        // ->isXmlHttpRequest()
        $this->diag('->isXmlHttpRequest()');

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->ok($request->isXmlHttpRequest(), '->isXmlHttpRequest() returns true if the method has HTTP_X_REQUESTED_WITH to XMLHttpRequest');

        // ->getCookie()
        $this->diag('->getCookie()');

        $_COOKIE['test'] = 'value';
        $request = new myRequest4($dispatcher);
        $this->is($request->getCookie('test'), 'value', '->getCookie() returns value of cookie');

        // ->getScriptName()
        $this->diag('->getScriptName()');

        $request = new myRequest4($dispatcher);
        $_SERVER['SCRIPT_NAME'] = '/frontend_test.php';
        $_SERVER['ORIG_SCRIPT_NAME'] = '/frontend_test2.php';
        $this->is($request->getScriptName(), '/frontend_test.php', '->getScriptName() returns the script name');

        $request = new myRequest4($dispatcher);
        unset($_SERVER['SCRIPT_NAME']);
        $_SERVER['ORIG_SCRIPT_NAME'] = '/frontend_test2.php';
        $this->is($request->getScriptName(), '/frontend_test2.php', '->getScriptName() returns the script name if SCRIPT_NAME not set it use ORIG_SCRIPT_NAME');

        $request = new myRequest4($dispatcher);
        unset($_SERVER['SCRIPT_NAME']);
        $this->is($request->getScriptName(), '', '->getScriptName() returns the script name if SCRIPT_NAME and ORIG_SCRIPT_NAME not set it return empty');

        // ->getPathInfo()
        $this->diag('->getPathInfo()');

        $request = new myRequest4($dispatcher);
        $options = $request->getOptions();
        $this->is($options['path_info_key'], 'PATH_INFO', 'check if default path_info_key is PATH_INFO');

        $request = new myRequest4($dispatcher);
        $_SERVER['PATH_INFO'] = '/test/klaus';
        $_SERVER['REQUEST_URI'] = '/test/klaus2';
        $this->is($request->getPathInfo(), '/test/klaus', '->getPathInfo() returns the url path value');

        $request = new myRequest4($dispatcher, array(), array(), array('path_info_key' => 'SPECIAL'));
        $_SERVER['SPECIAL'] = '/special';
        $this->is($request->getPathInfo(), '/special', '->getPathInfo() returns the url path value use path_info_key');
        $request->resetPathInfoArray();

        $request->resetPathInfoArray();
        $request = new myRequest4($dispatcher);
        $_SERVER['SCRIPT_NAME'] = '/frontend_test.php';
        $_SERVER['REQUEST_URI'] = '/frontend_test.php/test/klaus2';
        $_SERVER['QUERY_STRING'] = '';
        $this->is($request->getPathInfo(), '/test/klaus2', '->getPathInfo() returns the url path value if it not exists use default REQUEST_URI');

        $request = new myRequest4($dispatcher);
        $_SERVER['QUERY_STRING'] = 'test';
        $_SERVER['REQUEST_URI'] = '/frontend_test.php/test/klaus2?test';
        $this->is($request->getPathInfo(), '/test/klaus2', '->getPathInfo() returns the url path value if it not exists use default REQUEST_URI without query');

        $request->resetPathInfoArray();
        $request = new myRequest4($dispatcher);
        $this->is($request->getPathInfo(), '/', '->getPathInfo() returns the url path value if it not exists use default /');

        // -setRelativeUrlRoot() ->getRelativeUrlRoot()
        $this->diag('-setRelativeUrlRoot() ->getRelativeUrlRoot()');
        $this->is($request->getRelativeUrlRoot(), '', '->getRelativeUrlRoot() return computed relative url root');
        $request->setRelativeUrlRoot('toto');
        $this->is($request->getRelativeUrlRoot(), 'toto', '->getRelativeUrlRoot() return previously set relative url root');

        // ->addRequestParameters() ->getRequestParameters() ->fixParameters()
        $this->diag('->addRequestParameters() ->getRequestParameters() ->fixParameters()');

        $request = new myRequest4($dispatcher);
        $this->is($request->getRequestParameters(), array(), '->getRequestParameters() returns the request parameters default array');

        $request->addRequestParameters(array('test' => 'test'));
        $this->is($request->getRequestParameters(), array('test' => 'test'), '->getRequestParameters() returns the request parameters');

        $request->addRequestParameters(array('test' => 'test'));
        $this->is($request->getRequestParameters(), array('test' => 'test'), '->getRequestParameters() returns the request parameters allready exists');

        $request->addRequestParameters(array('test2' => 'test2', '_sf_ignore_cache' => 1));
        $this->is($request->getRequestParameters(), array('test' => 'test', 'test2' => 'test2', '_sf_ignore_cache' => 1), '->getRequestParameters() returns the request parameters check fixParameters call for special _sf_ params');
        $this->is($request->getAttribute('sf_ignore_cache'), 1, '->getAttribute() check special param is set as attribute');

        // ->getUrlParameter
        $this->diag('->getUrlParameter()');
        $this->is($request->getUrlParameter('test'), 'test', '->getUrlParameter() returns URL parameter by name');

        // ->checkCSRFProtection()
        $this->diag('->checkCSRFProtection()');

        sfForm::enableCSRFProtection();

        $request = new myRequest4($dispatcher);
        try {
            $request->checkCSRFProtection();
            $this->fail('->checkCSRFProtection() throws a validator error if CSRF protection fails');
        } catch (sfValidatorErrorSchema $error) {
            $this->pass('->checkCSRFProtection() throws a validator error if CSRF protection fails');
        }

        sfForm::setCSRFFieldName('_csrf_token'); // restore the default
        $request = new myRequest4($dispatcher);
        $request->setParameter('_csrf_token', '==TOKEN==');
        try {
            $request->checkCSRFProtection();
            $this->pass('->checkCSRFProtection() checks token from BaseForm');
        } catch (sfValidatorErrorSchema $error) {
            $this->fail('->checkCSRFProtection() checks token from BaseForm');
        }

// ->getContentType()
        $this->diag('->getContentType()');

        $request = new myRequest4($dispatcher);
        $_SERVER['CONTENT_TYPE'] = 'text/html';
        $this->is($request->getContentType(), 'text/html', '->getContentType() returns the content type');
        $request = new myRequest4($dispatcher);
        $_SERVER['CONTENT_TYPE'] = 'text/html; charset=UTF-8';
        $this->is($request->getContentType(), 'text/html', '->getContentType() strips the charset information by default');
        $this->is($request->getContentType(false), 'text/html; charset=UTF-8', '->getContentType() does not strip the charset information by defaultif you pass false as the first argument');

        // ->getReferer()
        $this->diag('->getReferer()');

        $request = new myRequest4($dispatcher);
        $_SERVER['HTTP_REFERER'] = 'http://domain';
        $this->is($request->getReferer(), 'http://domain', '->getContentType() returns the content type');

        // ->getHost()
        $this->diag('->getHost()');

        $request = new myRequest4($dispatcher);
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example1.com, example2.com, example3.com';
        $this->is($request->getHost(), 'example3.com', '->getHost() returns the last forwarded host');
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);

        $_SERVER['HTTP_HOST'] = 'symfony-project.org';
        $this->is($request->getHost(), 'symfony-project.org', '->getHost() returns the host');

        $request->setOption('trust_proxy', false);
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example1.com, example2.com, example3.com';
        $this->is($request->getHost(), 'symfony-project.org', '->getHost() returns the host even if forwarded host is define when "trust_proxy" option is set to false');
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);

        // ->getFiles()
        $this->diag('->getFiles()');

        $_FILES = array(
            'article' => array(
                'name' => array(
                    'media' => '1.png',
                ),
                'type' => array(
                    'media' => 'image/png',
                ),
                'tmp_name' => array(
                    'media' => '/private/var/tmp/phpnTrAJG',
                ),
                'error' => array(
                    'media' => 0,
                ),
                'size' => array(
                    'media' => 899,
                ),
            ),
        );
        $taintedFiles = array(
            'article' => array(
                'media' => array(
                    'error' => 0,
                    'name' => '1.png',
                    'type' => 'image/png',
                    'tmp_name' => '/private/var/tmp/phpnTrAJG',
                    'size' => 899,
                ),
            ),
        );
        $this->is_deeply($request->getFiles(), $taintedFiles, '->getFiles() return clean array extracted from $_FILES');
    }
}
