<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfMessageSource_Simple2 extends sfMessageSource
{
    public function __construct($source) {}

    public function delete($message, $catalogue = 'messages') {}

    public function update($text, $target, $comments, $catalogue = 'messages') {}

    public function catalogues() {}

    public function save($catalogue = 'messages') {}

    public function getId() {}
}
