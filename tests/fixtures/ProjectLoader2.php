<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ProjectLoader2 extends sfServiceContainerLoaderArray
{
    public function validate($content)
    {
        return parent::validate($content);
    }
}
