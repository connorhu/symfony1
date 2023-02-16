<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfMessageSource_Simple extends sfMessageSource_File
{
    protected $dataExt = '.xml';

    public function delete($message, $catalogue = 'messages') {}

    public function update($text, $target, $comments, $catalogue = 'messages') {}

    public function save($catalogue = 'messages') {}

    public function getCatalogueByDir($catalogue)
    {
        return parent::getCatalogueByDir($catalogue);
    }
}
