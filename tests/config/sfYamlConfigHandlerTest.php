<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixtures/myYamlConfigHandler.php';

/**
 * @internal
 *
 * @coversNothing
 */
class sfYamlConfigHandlerTest extends TestCase
{
    public function testMergeConfigValue()
    {
        $config = new myYamlConfigHandler();
        $config->initialize();

        $config->yamlConfig = array(
            'bar' => array(
                'foo' => array(
                    'foo' => 'foobar',
                    'bar' => 'bar',
                ),
            ),
            'all' => array(
                'foo' => array(
                    'foo' => 'fooall',
                    'barall' => 'barall',
                ),
            ),
        );
        $values = $config->mergeConfigValue('foo', 'bar');
        $this->assertSame('foobar', $values['foo'], '->mergeConfigValue() merges values for a given key under a given category');
        $this->assertSame('bar', $values['bar'], '->mergeConfigValue() merges values for a given key under a given category');
        $this->assertSame('barall', $values['barall'], '->mergeConfigValue() merges values for a given key under a given category');
    }

    public function testGetConfigValue()
    {
        $config = new myYamlConfigHandler();
        $config->initialize();

        $config->yamlConfig = array(
            'bar' => array(
                'foo' => 'foobar',
            ),
            'all' => array(
                'foo' => 'fooall',
            ),
        );
        $this->assertSame('foobar', $config->getConfigValue('foo', 'bar'), '->getConfigValue() returns the value for the key in the given category');
        $this->assertSame('fooall', $config->getConfigValue('foo', 'all'), '->getConfigValue() returns the value for the key in the given category');
        $this->assertSame('fooall', $config->getConfigValue('foo', 'foofoo'), '->getConfigValue() returns the value for the key in the "all" category if the key does not exist in the given category');
        $this->assertSame('default', $config->getConfigValue('foofoo', 'foofoo', 'default'), '->getConfigValue() returns the default value if key is not found in the category and in the "all" category');
    }
}
