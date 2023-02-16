<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class OutputEscaperTest
{
    public function __toString()
    {
        return $this->getTitle();
    }

    public function getTitle()
    {
        return '<strong>escaped!</strong>';
    }

    public function getTitles()
    {
        return array(1, 2, '<strong>escaped!</strong>');
    }
}
