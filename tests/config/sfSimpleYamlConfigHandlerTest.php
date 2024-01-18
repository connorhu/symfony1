<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

function get_retval($config, $files)
{
    $retval = $config->execute($files);
    $retval = preg_replace('#^<\?php#', '', $retval);
    $retval = preg_replace('#<\?php$#s', '', $retval);

    return eval($retval);
}

/**
 * @internal
 *
 * @coversNothing
 */
class sfSimpleYamlConfigHandlerTest extends TestCase
{
    public function test()
    {
        $config = new sfSimpleYamlConfigHandler();
        $config->initialize();

        $fixturesDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures');
        $dir = $fixturesDir.DIRECTORY_SEPARATOR.'sfSimpleYamlConfigHandler'.DIRECTORY_SEPARATOR;

        $array = get_retval($config, array($dir.'config.yml'));
        $this->assertSame('foo', $array['article']['title'], '->execute() returns configuration file as an array');

        $array = get_retval($config, array($dir.'config.yml', $dir.'config_bis.yml'));
        $this->assertSame('bar', $array['article']['title'], '->execute() returns configuration file as an array');
    }
}
