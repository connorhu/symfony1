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
class sfCoreAutoloadTest extends TestCase
{
    public function testClassPath()
    {
        $autoload = sfCoreAutoload::getInstance();
        $this->assertSame($autoload->getBaseDir().'/action/sfAction.class.php', $autoload->getClassPath('sfaction'), '"sfCoreAutoload" is case-insensitive');
        $this->assertSame($autoload->getBaseDir().'/action/sfAction.class.php', $autoload->getClassPath('sFaCTiOn'), '"sfCoreAutoload" is case-insensitive');
    }
}
