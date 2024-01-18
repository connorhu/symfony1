<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class sfDefineEnvironmentConfigHandlerTest extends TestCase
{
    public function testPrefix()
    {
        sfConfig::set('sf_symfony_lib_dir', realpath(__DIR__.'/../../../lib'));

        // prefix
        $handler = new sfDefineEnvironmentConfigHandler();
        $handler->initialize(array('prefix' => 'sf_'));

        $dir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'sfDefineEnvironmentConfigHandler');

        $files = array(
            $dir.DIRECTORY_SEPARATOR.'prefix_default.yml',
            $dir.DIRECTORY_SEPARATOR.'prefix_all.yml',
        );

        sfConfig::set('sf_environment', 'prod');

        $data = $handler->execute($files);
        $data = preg_replace('#date\: \d+/\d+/\d+ \d+\:\d+\:\d+#', '', $data);

        $this->assertSame($data, str_replace("\r\n", "\n", file_get_contents($dir.DIRECTORY_SEPARATOR.'prefix_result.php')));
    }
}
