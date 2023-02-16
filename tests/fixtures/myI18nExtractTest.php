<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @internal
 *
 * @coversNothing
 */
class myI18nExtractTest extends sfI18nExtract
{
    public function extract()
    {
        $this->updateMessages($this->getMessages());
    }

    public function getMessages()
    {
        return array('toto', 'an english sentence');
    }
}
