<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/myConfigHandler.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfConfigHandlerTest extends TestCase
{
    public function testInitialize()
    {
        $config = new myConfigHandler();
        $config->initialize();

        $config->initialize(array('foo' => 'bar'));
        $this->assertSame('bar', $config->getParameterHolder()->get('foo'), '->initialize() takes an array of parameters as its first argument');
    }

    public function testReplaceConstants()
    {
        $config = new myConfigHandler();
        $config->initialize();

        sfConfig::set('foo', 'bar');
        $this->assertSame('my value with a bar constant', sfConfigHandler::replaceConstants('my value with a %foo% constant'), '::replaceConstants() replaces constants enclosed in %');
        $this->assertSame('%Y/%m/%d %H:%M', sfConfigHandler::replaceConstants('%Y/%m/%d %H:%M'), '::replaceConstants() does not replace unknown constants');

        sfConfig::set('foo', 'bar');
        $value = array(
            'foo' => 'my value with a %foo% constant',
            'bar' => array(
                'foo' => 'my value with a %foo% constant',
            ),
        );
        $value = sfConfigHandler::replaceConstants($value);
        $this->assertSame('my value with a bar constant', $value['foo'], '::replaceConstants() replaces constants in arrays recursively');
        $this->assertSame('my value with a bar constant', $value['bar']['foo'], '::replaceConstants() replaces constants in arrays recursively');
    }

    public function testGetParameterHolder()
    {
        $config = new myConfigHandler();
        $config->initialize();

        $this->assertInstanceOf(sfParameterHolder::class, $config->getParameterHolder(), '->getParameterHolder() returns a parameter holder instance');
    }

    public function testReplacePath()
    {
        $config = new myConfigHandler();
        $config->initialize();

        sfConfig::set('sf_app_dir', 'ROOTDIR');
        $this->assertSame($config->replacePath('test'), 'ROOTDIR/test', '->replacePath() prefix a relative path with "sf_app_dir"');
        $this->assertSame($config->replacePath('/test'), '/test', '->replacePath() prefix a relative path with "sf_app_dir"');
    }
}
