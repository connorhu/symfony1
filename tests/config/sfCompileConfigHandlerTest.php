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
class sfCompileConfigHandlerTest extends TestCase
{
    public function testExecute()
    {
        $handler = new sfCompileConfigHandler();
        $handler->initialize();

        $dir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'sfCompileConfigHandler');

        sfConfig::set('sf_debug', true);
        $data = $handler->execute(array($dir.DIRECTORY_SEPARATOR.'simple.yml'));
        $this->assertSame(true, false !== strpos($data, "class sfInflector\n{\n    /**"), '->execute() return complete classe codes');

        sfConfig::set('sf_debug', false);
        $data = $handler->execute(array($dir.DIRECTORY_SEPARATOR.'simple.yml'));
        $this->assertSame(true, false !== strpos($data, "class sfInflector\n{\n    public"), '->execute() return minified classe codes');
    }
}
