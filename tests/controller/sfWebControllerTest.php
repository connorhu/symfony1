<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../sfContext.class.php';
require_once __DIR__.'/../sfNoRouting.class.php';
require_once __DIR__.'/../fixtures/myWebResponse.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfWebControllerTest extends TestCase
{
    private sfFrontWebController $controller;
    private sfContext $context;

    protected function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        sfConfig::set('sf_max_forwards', 10);
        $this->context = sfContext::getInstance(array(
            'routing' => 'sfNoRouting',
            'request' => 'sfWebRequest',
            'response' => 'myWebResponse',
        ), true);

        $this->controller = new sfFrontWebController($this->context, null);
    }

    /** @dataProvider convertUrlStringToParametersDataProvider */
    public function testConvertUrlStringToParameters(string $url, array $expected)
    {
        $message = sprintf('->convertUrlStringToParameters() converts a symfony internal URI to an array of parameters (%s)', $url);
        $this->assertSame($expected, $this->controller->convertUrlStringToParameters($url), $message);
    }

    public function convertUrlStringToParametersDataProvider(): Generator
    {
        yield array('module/action', array(
            '',
            array(
                'module' => 'module',
                'action' => 'action',
            ),
        ));

        yield array('module/action?id=12', array(
            '',
            array(
                'module' => 'module',
                'action' => 'action',
                'id' => '12',
            ),
        ));

        yield array('module/action?id=12&', array(
            '',
            array(
                'module' => 'module',
                'action' => 'action',
                'id' => '12&',
            ),
        ));

        yield array('module/action?id=12&test=4&toto=9', array(
            '',
            array(
                'module' => 'module',
                'action' => 'action',
                'id' => '12',
                'test' => '4',
                'toto' => '9',
            ),
        ));

        yield array('module/action?id=12&test=4&5&6&7&&toto=9', array(
            '',
            array(
                'module' => 'module',
                'action' => 'action',
                'id' => '12',
                'test' => '4&5&6&7&',
                'toto' => '9',
            ),
        ));

        yield array('module/action?test=value1&value2&toto=9', array(
            '',
            array(
                'module' => 'module',
                'action' => 'action',
                'test' => 'value1&value2',
                'toto' => '9',
            ),
        ));

        yield array('module/action?test=value1&value2', array(
            '',
            array(
                'module' => 'module',
                'action' => 'action',
                'test' => 'value1&value2',
            ),
        ));

        yield array('module/action?test=value1=value2&toto=9', array(
            '',
            array(
                'module' => 'module',
                'action' => 'action',
                'test' => 'value1=value2',
                'toto' => '9',
            ),
        ));

        yield array('module/action?test=value1=value2', array(
            '',
            array(
                'module' => 'module',
                'action' => 'action',
                'test' => 'value1=value2',
            ),
        ));

        yield array('module/action?test=4&5&6&7&&toto=9&id=', array(
            '',
            array(
                'module' => 'module',
                'action' => 'action',
                'test' => '4&5&6&7&',
                'toto' => '9',
                'id' => '',
            ),
        ));

        yield array('@test?test=4', array(
            'test',
            array(
                'test' => '4',
            ),
        ));

        yield array('@test', array(
            'test',
            array(
            ),
        ));

        yield array('@test?id=12&foo=bar', array(
            'test',
            array(
                'id' => '12',
                'foo' => 'bar',
            ),
        ));

        yield array('@test?id=foo%26bar&foo=bar%3Dfoo', array(
            'test',
            array(
                'id' => 'foo&bar',
                'foo' => 'bar=foo',
            ),
        ));
    }

    public function testParseError()
    {
        $this->expectException(sfParseException::class);
        $this->controller->convertUrlStringToParameters('@test?foobar');
    }

    public function testRedirect()
    {
        $this->controller->redirect('module/action?id=1#photos');
        $response = $this->context->getResponse();
        $this->assertMatchesRegularExpression('~http\://localhost/index.php/\?module=module&amp;action=action&amp;id=1#photos~', $response->getContent(), '->redirect() adds a refresh meta in the content');
        $this->assertMatchesRegularExpression('~http\://localhost/index.php/\?module=module&action=action&id=1#photos~', $response->getHttpHeader('Location'), '->redirect() adds a Location HTTP header');
    }

    public function testRedirectWithNull()
    {
        try {
            $this->controller->redirect(null);

            $this->assertTrue(false, '->redirect() throw an InvalidArgumentException when the url argument is null');
        } catch (InvalidArgumentException $iae) {
            $this->assertTrue(true, '->redirect() throw an InvalidArgumentException when the url argument is null');
        } catch (Exception $e) {
            $this->assertTrue(false, '->redirect() throw an InvalidArgumentException when the url argument is null. '.get_class($e).' was received');
        }
    }

    public function testRedirectWithEmpty()
    {
        try {
            $this->controller->redirect('');

            $this->assertTrue(false, '->redirect() throw an InvalidArgumentException when the url argument is an empty string');
        } catch (InvalidArgumentException $iae) {
            $this->assertTrue(true, '->redirect() throw an InvalidArgumentException when the url argument is an empty string');
        } catch (Exception $e) {
            $this->assertTrue(false, '->redirect() throw an InvalidArgumentException when the url argument is an empty string. '.get_class($e).' was received');
        }
    }

    public function testGenUrl()
    {
        $expected = $this->controller->genUrl(array('action' => 'action', 'module' => 'module', 'id' => 4));
        $actual = $this->controller->genUrl('module/action?id=4');
        $this->assertSame($expected, $actual, '->genUrl() accepts a string or an array as its first argument');
    }

    public function testGenUrlWithEmptyString()
    {
        $lastError = error_get_last();
        $this->controller->genUrl('');
        $this->assertSame(error_get_last(), $lastError, '->genUrl() accepts an empty string');
    }
}
